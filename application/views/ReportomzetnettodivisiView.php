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
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container">
		<div class="row">
			<div class="col-3 col-m-3">Periode</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" required>
			</div>
		</div> 
		<div class="row">
			<div class="col-3 col-m-3">Divisi</div>
			<div class="col-5 col-m-2 date">
				<select id="cboDivisi" name="cboDivisi" class="form-control">
            <option value='ALL' selected>ALL</option>
            <?php
              for($i=0;$i<count($divisi);$i++){
                echo "<option value='".$divisi[$i]->Kd_Divisi."'>".$divisi[$i]->Nama_Divisi."</option>";
              }
            ?>
          </select>
			</div>
		</div>  
		<div class="row">
			<div class="col-3 col-m-3">Partner Type</div>
			<div class="col-5 col-m-2 date">
				<select id="cboPartnerType" name="cboPartnerType" class="form-control">
            <option value='ALL' selected>ALL</option>
            <?php
              for($i=0;$i<count($partnertype);$i++){
                echo "<option value='".$partnertype[$i]->PARTNER_TYPE."'>".$partnertype[$i]->PARTNER_TYPE."</option>";
              }
            ?>
          </select>
			</div>
		</div>  
		<div class="row">
			<div class="col-3 col-m-3">Wilayah</div>
			<div class="col-5 col-m-2 date">
				<select id="cboWilayah" name="cboWilayah" class="form-control">
            <option value='ALL' selected>ALL</option>
            <?php
              for($i=0;$i<count($wilayah);$i++){
                echo "<option value='".$wilayah[$i]->Kode_Wilayah."'>".$wilayah[$i]->Nama_Wilayah."</option>";
              }
            ?>
          </select>
			</div>
		</div>  
		<div class="row">
			<div class="col-3 col-m-3">Kategori Barang</div>
			<div class="col-5 col-m-2 date">
				<select id="cboKategoriBarang" name="cboKategoriBarang" class="form-control"> 
            <option value='ALL' selected>PRODUCT & SPAREPART</option>
            <option value='P'>PRODUCT</option>
            <option value='S'>SPAREPART</option>
          </select>
			</div>
		</div>  
		<div class="row">
			<div class="col-3 col-m-3">Tipe Faktur</div>
			<div class="col-5 col-m-2 date">
				<select id="cboTipeFaktur" name="cboTipeFaktur" class="form-control"> 
            <option value='ALL' selected>ALL</option>
            <?php
              for($i=0;$i<count($tipefaktur);$i++){
                echo "<option value='".$tipefaktur[$i]->Tipe_Faktur."'>".$tipefaktur[$i]->Tipe_Faktur."</option>";
              }
            ?>
          </select>
			</div>
		</div>  

		<div class="row">
         <div class="col-3">
            Report
         </div>
         <div class="col-9 col-md-8">
            <input type="radio" name="report" id="report1" value="1" checked> <label for="report1">PROSES OMZET NETTO PER DIVISI</label> &nbsp;&nbsp;&nbsp;
            <input type="radio" name="report" id="report2" value="2"> <label for="report2">PROSES OMZET NETTO PER WILAYAH</label>
         </div>
      </div>
      <div class="row" align="center" style="padding-top:50px;" id="div_pdf">  
         <input type = "submit" name="btnExcel" value="EXPORT EXCEL"/>
         <input type = "submit" name="btnPdf" value="EXPORT PDF"/>
      </div>
    </div>
	<?php echo form_close(); ?>
</div> <!-- /container -->
<script type="text/javascript">
 
	 
</script>
	 
	  
	  
	  
 
 


