<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	/* .row-label {
    padding-left: 15px;
    width:180px;
	} */
	.row-input {
    width:420px;
	}


</style>


<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>

	<?php  
        echo form_open("Reportjualreturparentdivsummary/Reportjualreturparentdivsummary_Proses", array("target"=>"_blank"));		
	?>

	<div class="form-container">
		        
        <div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp1" placeholder="dd/mm/yyyy" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">-- S/D --</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp2" placeholder="dd/mm/yyyy" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Parent Divisi</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="parentdiv" id="parentdiv" >
                    <option value='all' selected>ALL</option>

                    <?php 
						$jum= count($listparentdiv->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listparentdiv->data[$i]->PARENTDIV."'
                                >".$listparentdiv->data[$i]->PARENTDIV."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Partner Type</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="partnertype" id="partnertype" >
                    <option value='all' selected>ALL</option>

                    <?php 
						$jum= count($listpartnertype->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listpartnertype->data[$i]->PARTNER_TYPE."'
                                >".$listpartnertype->data[$i]->PARTNER_TYPE."</option>";
						}			  
					?>
				</select>
			</div>
		</div>


        <div class="row">
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="wilayah" id="wilayah" >
                    <option value='all' selected>ALL</option>

                    <?php 
						$jum= count($listwilayah->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listwilayah->data[$i]->WILAYAH."'
                                >".$listwilayah->data[$i]->WILAYAH."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Kategori Barang</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="kategoribrg" id="kategoribrg" >
                    <option value="all">ALL</option>
                    <option value="p">Produk</option>
                    <option value="s">Sparepart</option>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Tipe Faktur</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="tipefaktur" id="tipefaktur" >
                    <option value='all' selected>ALL</option>

                    <?php 
						$jum= count($listtipefaktur->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listtipefaktur->data[$i]->Tipe_Faktur."'
                                >".$listtipefaktur->data[$i]->Tipe_Faktur."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

		<div class="row">
		<div class="col-3"> </div>
			<div class="col-8">
				<input type="checkbox" id="perkategoriinsentif" name="perkategoriinsentif" value="Y" >
				<label for="perkategoriinsentif">Per Kategori Insentif</label>
			</div>	
		</div>	

        <div class="row">
			<div class="col-3">Report</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="report" id="report" >
                    <option value="0">LAPORAN PER PARENT DIVISI PER WILAYAH</option>
                    <option value="1">LAPORAN PER WILAYAH PER PARENT DIVISI </option>
					<option value="2">LAPORAN PER DEALER SUMMARY</option>
					<option value="3">LAPORAN PER PARENT DIVISI PER PARTNER TYPE</option>
					<option value="4">LAPORAN PER PARTNER TYPE PER PARENT DIVISI </option>
				</select>
			</div>
		</div>
        		

        <div class="row" align="center" style="padding-top:50px;">		
            <input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />	
			<input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>


<script>
    
    $(document).ready(function() {
        $('#dp1').datepicker({
            format: "dd-mm-yyyy",
            autoclose: true
        }).on('changeDate', function(e) { //changeDate
            var StartDt = $('#dp1').datepicker('getDate');
            $('#dp2').datepicker("setStartDate", StartDt);
        });

        $('#dp2').datepicker({
            format: "dd-mm-yyyy",
            autoclose: true
        }).on('changeDate', function(e) { //changeDate
            var EndDt = $('#dp2').datepicker('getDate');
            $('#dp1').datepicker("setEndDate", EndDt);
        });
    } );





</script>






