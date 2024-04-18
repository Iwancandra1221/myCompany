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
	#no-rekening-value{
		color:black;
	}
	.datepicker{
		z-index: 100000;
	}
	
</style>

<script>
    $(document).ready(function() {
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

	} );
</script>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open($formUrl, array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
           	<div class="col-3">Periode</div>
        	<div class="col-9">
        		<input type="text" name="periode_start" id="dp1" value="<?=date('m/d/Y')?>" style="color:black;"> s/d
        		<input type="text" name="periode_end" id="dp2" value="<?=date('m/d/Y')?>" style="color:black;">
     		</div>
        </div>
        <div class="row">
        	<div class="col-3">Parent Divisi</div>
        	<div class="col-6">
        		<select name="parent_divisi" style="width:100%;">
        			<?php
        			if($parentDivisi!=null){
        				foreach($parentDivisi as $value){
        					echo <<<HTML
        					<option value="{$value['PARENTDIV']}">{$value['PARENTDIV']}</option>
HTML;
        				}
        			}
        			?>
        		</select>
        	</div>
        </div>
        <div class="row">
            <div class="col-3">Partner Type</div>
            <div class="col-6">
                <select name="partner_type" style="width:100%;">
                    <?php
                    if($partnerType!=null){
                        echo '<option value="">ALL</option>';
                        foreach($partnerType as $value){
                            echo <<<HTML
                            <option value="{$value['PARTNER_TYPE']}">{$value['PARTNER_TYPE']}</option>
HTML;
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
		<div class="row">
        	<div class="col-3">Wilayah</div>
        	<div class="col-6">
        		<select name="kd_wil" style="width:100%;">
        			<?php
        			if($wilayah!=null){
        				foreach($wilayah as $value){
        					echo <<<HTML
        					<option value="{$value['Kode_Wilayah']}">{$value['Nama_Wilayah']}</option>
HTML;
        				}
        			}
        			?>
        		</select>
        	</div>
        </div>
        <div class="row">
        	<div class="col-3">Kategori BRG</div>
        	<div class="col-6">
        		<select name="kat_brg" style="width:100%;">
        			<option value="">ALL</option>
        			<option value="P">PRODUCT</option>
        			<option value="S">SPAREPART</option>
        		</select>
        	</div>
        </div>
        <div class="row">
        	<div class="col-3">Tipe Faktur</div>
        	<div class="col-6">
        		<select name="tipe_faktur" style="width:100%;">
        			<?php
        			if($tipeFaktur!=null){
        				foreach($tipeFaktur as $value){
        					echo <<<HTML
        					<option value="{$value['Tipe_Faktur']}">{$value['Tipe_Faktur']}</option>
HTML;
        				}
        			}
        			?>
        		</select>
        	</div>
        </div>
        <div class="row">
    		<div class="col-3 col-m-3 row-label"></div>
        	<div class="row-input">
        		<input type="radio" name="option1" value="1" checked="checked">
              	<label>DEALER</label>
              	<br>
              	<input type="radio" name="option1" value="2">
              	<label>KOTA</label>
        	</div>
        </div>
        <div class="row">
           	<div class="col-3 col-m-3 row-label">Tipe Report</div>
        	<div class="row-input">
              	<input type="radio" name="tipe_laporan" value="1" checked="checked">
              	<label>LAPORAN DEALER PER PARENT DIVISI</label>
              	<br>
              	<input type="radio" name="tipe_laporan" value="2">
              	<label>LAPORAN PARENT DIVISI PER DEALER</label>
              	<br>
				<input type="radio" name="tipe_laporan" value="3">
              	<label>LAPORAN PARENTDIV PER DEALER PER DIVISI</label>
			</div>           
        </div>
        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="submit" value="EXPORT PDF"/>
			<input type="submit" name="submit" value="EXPORT EXCEL"/>
			<!-- <input type="submit" name="submit" value="EXCEL"/> -->
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->