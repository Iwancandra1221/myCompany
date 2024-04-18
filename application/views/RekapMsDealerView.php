<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container"> 
      <div class="row" id="div_partner_type">
			<div class="col-3">Partner Type</div>
			<div class="col-8 col-m-8 date">
				<select class="form-control" name="partnertype" id="partnertype" novalidate> 
					<?php 
						foreach($listpartnertype as $s)
						{
							echo("<option value='".$s->Kode_Partnertype."'>".$s->Nama_Partnertype."</option>"); 
						}			  
					?>
				</select>
			</div>
		</div>  
		 <div class="row" id="div_semua_wilayah">
			<div class="col-3"></div>
			<div class="col-8 col-m-8 date"> 
         	<input type="checkbox" id="cbox1" name="cbox1" checked  onclick="handleClick(this,0)">
         	<input type="hidden" id="statuscbox1" name="statuscbox1" value="Y" >
         	<label for="cbox1">Semua Wilayah</label>
			</div>
		</div>   
      <div class="row" id="div_wilayah_ho">
			<div class="col-3">Wilayah HO</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="wilayahho" id="wilayahho" novalidate> 
					<?php 
						foreach($listwilayahho as $s)
						{
							echo("<option value='".$s->Kode_Wilayah."'>".$s->Nama_Wilayah."</option>"); 
						}			  
					?>
				</select>
			</div>
		</div>  
		 <div class="row" id="div_semua_wp_shipment">
			<div class="col-3"></div>
			<div class="col-8 col-m-8 date"> 
         	<input type="checkbox" id="cbox2" name="cbox2" checked onclick="handleClick(this,1)">
         	<input type="hidden" id="statuscbox2" name="statuscbox2" value="Y" >
         	<label for="cbox2">Semua W.P. Shipment</label>
			</div>
		</div>   
      <div class="row" id="div_wp_shipment">
			<div class="col-3">W.P. Shipment</div>
			<div class="col-8 col-m-8 date">
				<select class="form-control" name="wpshipment" id="wpshipment" novalidate> 
					<?php 
						foreach($listwpshipment as $s)
						{
							echo("<option value='".$s->Kode_Wilayah."'>".$s->Nama_Wilayah."</option>"); 
						}			  
					?>
				</select>
			</div>
		</div>  
  		<div class="row">
         <div class="col-3"> 
         </div>
         <div class="col-9 col-md-8">
            <input type="radio" name="status" id="radio_aktif" value="Y" checked>  
				<label for="radio_aktif">Aktif</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="status" id="radio_non_aktif" value="N" > 
            <label for="radio_non_aktif">Tidak Aktif</label> &nbsp;&nbsp;&nbsp; 
            <input type="radio" name="status" id="radio_all" value="ALL" > 
            <label for="radio_all">ALL</label> 
         </div>     
      </div> 

      <div class="row" align="center" style="padding-top:50px;" id="div_excel"> 
         <input type = "submit" name="btnExcel" value="EXPORT EXCEL"/> 
      </div> 
    </div>
	<?php echo form_close(); ?>
</div>  
<script type="text/javascript"> 

 	$(document).ready(function() { 
		$("#wilayahho").prop('disabled', true);
		$("#wpshipment").prop('disabled', true);
	} );

	function handleClick(cb,from) {  
		if (from==0)
		{
			$("#wilayahho").prop('disabled', cb.checked); 
			if (cb.checked) 
			{ 
				$('#statuscbox1').val("Y");
			}
			else 
			{ 
				$('#statuscbox1').val("N");
			}
		}
		else 
		{
			$("#wpshipment").prop('disabled', cb.checked); 
			if (cb.checked) 
			{ 
				$('#statuscbox2').val("Y");
			}
			else 
			{ 
				$('#statuscbox2').val("N");
			}
		} 
	} 
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
	 
	  
	  
	  
 
 


