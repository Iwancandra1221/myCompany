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
</style>

<script>
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd-M-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "dd-M-yyyy",
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
        echo form_open(site_url('ReportOmzet/ProsesReportOmzetNettoDealerDivisi'), array("target"=>"_blank"));
	?>
	<div class="form-container">
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Laporan</div>
        	<div class="col-9 col-m-8">
        		<select name="laporan" class="form-control" width="100%">
					<option value="LaporanDealerPerDivisi">LAPORAN DEALER PER DIVISI</option>
					<option value="LaporanDivisiPerDealer">LAPORAN DIVISI PER DEALER</option>
					<option value="LaporanDealerPerAlmKirimPerDivisi">LAPORAN DEALER PER ALM KIRIM PER DIVISI</option>
					<option value="LaporanDealerPerKotaPerAlmKirimPerDivisi">LAPORAN DEALER PER KOTA PER ALM KIRIM PER DIVISI</option>
					<option value="LaporanKotaPerDivisi">LAPORAN KOTA PER DIVISI</option>
					<option value="LaporanDivisiPerKota">LAPORAN DIVISI PER KOTA</option>     
				</select>
        	</div>
        </div>
		<div class="row">
			<div class="col-3 col-m-3 row-label">Tanggal Dari</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp1" placeholder="dd-M-yyyy" name="dp1" value="<?php echo date('d-M-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-3 col-m-3 date">
				<input type="text" class="form-control" id="dp2" placeholder="dd-M-yyyy" name="dp2" value="<?php echo date('d-M-Y'); ?>" autocomplete="off" required>
			</div>
		</div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Partner Type</div>
        	<div class="col-9 col-m-8">
        		<select name="partner_type" class="form-control" width="100%">
					<option value="ALL">ALL</option>
					<?php
					if($partner_type!=null){
						foreach($partner_type as $p){
						echo '<option value="'.$p['partner_type_code'].'">'.$p['partner_type_name'].'</option>';
						}
					}
					?>
				</select>
        	</div>
        </div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Wilayah</div>
        	<div class="col-9 col-m-8">
        		<select name="wilayah" class="form-control" width="100%">
					<option value="ALL">ALL</option>
					<?php
					if($wilayah!=null){
						foreach($wilayah as $w){
						echo '<option value="'.$w['Wilayah'].'">'.$w['Wilayah'].'</option>';
						}
					}
					?>
				</select>
        	</div>
        </div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Divisi</div>
        	<div class="col-9 col-m-8">
        		<select name="divisi" class="form-control" width="100%">
					<option value="ALL">ALL</option>
					<?php
					if($divisi!=null){
						foreach($divisi as $d){
						echo '<option value="'.$d['Divisi'].'">'.$d['Divisi'].'</option>';
						}
					}
					?>
				</select>
        	</div>
        </div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Tipe Faktur</div>
        	<div class="col-9 col-m-8">
        		<select name="tipe_faktur" class="form-control" width="100%">
					<option value="SEMUA">SEMUA</option>
					<?php
					if($tipe_faktur!=null){
						foreach($tipe_faktur as $t){
							$selected = '';
							if(rtrim($t['Tipe_Faktur'])=='R'){
								$selected = 'selected';
							}
							echo '<option value="'.$t['Tipe_Faktur'].'" '.$selected.'>'.$t['Tipe_Faktur'].'</option>';
						}
					}
					?>
				</select>
        	</div>
        </div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Kategori</div>
        	<div class="col-9 col-m-8">
        		<select name="kategori_brg" class="form-control" width="100%">
					<option value="">PRODUCT & SPAREPART</option>
					<option value="P">PRODUCT</option>
					<option value="S">SPAREPART</option>
				</select>
        	</div>
        </div>
		<!-- <div class="row">
           	<div class="col-3 col-m-3 row-label"></div>
        	<div class="col-9 col-m-8">
        		<input type="checkbox" name="x" value="1"> **
        	</div>
        </div> -->
		<div class="row">
           	<div class="col-3 col-m-3 row-label"></div>
        	<div class="col-9 col-m-8">
				<input type="submit" name="preview" value="PREVIEW"/>
				<input type="submit" name="excel" value="EXCEL"/>
        	</div>
        </div>
	</div>
	<?php echo form_close(); ?>
</div> <!-- /container -->