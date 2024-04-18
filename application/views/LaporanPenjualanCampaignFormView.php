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
<div class="">
	<?php 
        echo form_open("LaporanPenjualanCampaign/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container" style="height:150px!important;">
	
		<div class="row">
			<div class="col-3">Pilih Campaign</div>
			<div class="col-9 col-m-8">
				<select  class="form-control" name="campaign" id="campaign" required>
					<option value=""></option>
					<?php 
						foreach($campaign as $c)
						{
							echo("<option value='".$c->jns_trx.'###'.$c->nm_trx."'>".$c->jns_trx." - ".$c->nm_trx."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
		
        <div class="row" align="center" style="padding-top:0px;">
			<input type="submit" name="btnPreview" value="PREVIEW"/>
			<input type="submit" name="btnExcel" value="EXCEL"/>
		</div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->
