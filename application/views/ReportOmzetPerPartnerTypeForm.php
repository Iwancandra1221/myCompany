<script>
    //Form Load
    $(document).ready(function() {
        $(".select2").select2({
            
          }); 
    } );
</script>

<div class="container">
	<div class="page-title"><?php echo($opt);?></div>
	  <?php 
	    echo form_open($formURL, array("target"=>"_blank")) 
	  ?>
  	<div class="form-container">
   <div class="row PERIODE" style="">
       <div class="col-3 col-m-4" align="right">
          Tahun
       </div>
       <div class="col-9 col-m-8">
          <input style="color:black" type="text" name="Tahun" id="Tahun" value="<?php echo(date('Y'));?>">
       </div>
    </div>
    <div class="row PERIODE" style="">
       <div class="col-3 col-m-4" align="right">
          Bulan
       </div>
       <div class="col-9 col-m-8">
          <select name="Bulan" id="Bulan">
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
          </select> 
       </div>
    </div>
	
    <?php if ($BE_Java == 0) { ?>
		<div class="row">
			<div class="col-3" align="right">Divisi</div>
			<div class="col-9 col-m-8">
				<select  class="form-control" name="divisi" required>
					<?php 
						foreach($divisi as $s)
						{
							echo("<option value='".$s->divisi."'>".$s->divisi."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
    <?php } else { ?>  
      <?php if ($BE_Java_Multi_Filter == 0) { ?>
        <div class="row">
			<div class="col-3" align="right">Divisi</div>
			<div class="col-9 col-m-8">
				<select  class="form-control" name="divisi" required>
					<?php 
						foreach($divisi as $s)
						{
							echo("<option value='".$s->divisi."'>".$s->divisi."</option>");
						}			  
					?>
				</select>
			</div>
		</div>
    <?php } else { ?>       
    <div class="row">
        <div class="col-3" align="right">Divisi</div>
        <div class="col-8 col-m-8 date">
            <select class="form-control select2" name="divisi[]" id="divisi[]" multiple="multiple" required>
            <!-- <option value="ALL">ALL</option> -->
            <?php 
              foreach($divisi as $s)
              {
                echo("<option value='".trim($s->divisi)."'>".trim($s->divisi)."</option>");
              }			  
            ?>
            </select>
        </div>
    </div>
    <?php } ?> 
    <?php } ?> 

    <?php if ($BE_Java == 0) { ?>
		<div class="row">
			<div class="col-3" align="right">Partner Type</div>
			<div class="col-9 col-m-8"> 
          <select class="form-control" name="PartnerType" id="PartnerType">
			      <option value="TRADISIONAL" >TRADISIONAL</option>
            <option value="MODERN OUTLET" >COUNTER</option>
            <option value="MODERN OUTLET" >MODERN OUTLET</option>
            <option value="MO CABANG" >MO CABANG</option>
            <option value="PROYEK" >PROYEK</option>
			</select>
			</div>
		</div>
    <?php } else { ?> 
    <?php if ($BE_Java_Multi_Filter == 0) { ?>
      <div class="row">
			<div class="col-3" align="right">Partner Type</div>
			<div class="col-9 col-m-8"> 
          <select class="form-control" name="PartnerType" id="PartnerType">
			      <option value="TRADISIONAL" >TRADISIONAL</option>
            <option value="MODERN OUTLET" >COUNTER</option>
            <option value="MODERN OUTLET" >MODERN OUTLET</option>
            <option value="MO CABANG" >MO CABANG</option>
            <option value="PROYEK" >PROYEK</option>
			</select>
			</div>
		</div>
    <?php } else { ?> 
    <div class="row">
        <div class="col-3" align="right">Partner Type</div>
        <div class="col-8 col-m-8 date">
            <select class="form-control select2" name="PartnerType[]" id="PartnerType[]" multiple="multiple" required>
			      <option value="TRADISIONAL" >TRADISIONAL</option>
            <option value="MODERN OUTLET" >COUNTER</option>
            <option value="MODERN OUTLET" >MODERN OUTLET</option>
            <option value="MO CABANG" >MO CABANG</option>
            <option value="PROYEK" >PROYEK</option>
            </select>
        </div>
    </div>
		<?php } ?> 
    <?php } ?> 

    <div class="row" align="center">
      <div class="col-12 col-m-12">
        <input type = "submit" name="btnExcel" value="EXCEL"/>
      </div>
    </div>
  </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>
</div> 