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
          <input type="text" name="Tahun" id="Tahun" value="<?php echo(date('Y'));?>">
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
  
    <div class="row">
      <div class="col-3" align="right">Divisi</div>
      <div class="col-9 col-m-8">
        <select  class="form-control" name="divisi" required>
          <option value="ALL">ALL</option>
          <?php 
            foreach($divisi as $s)
            {
              echo("<option value='".$s->divisi."'>".$s->divisi."</option>");
            }       
          ?>
        </select>
      </div>
    </div>
    
    <div class="row">
      <div class="col-3 opt_wilayah" align="right">Wilayah</div>
      <!-- <label class="col-1">&nbsp;</label>  -->
      <div class="col-9 col-m-8">
        <select  class="form-control opt_wilayah" name="wilayah" required>
          <option value="ALL">ALL</option>
          <?php 
            foreach($wilayah as $s)
            {
              echo("<option value='".$s->wilayah."'>".$s->wilayah."</option>");
            }       
          ?>
        </select>
      </div>
    </div>
    
    <div class="row">
      <div class="col-3 opt_wilayah" align="right">Kota</div>
      <!-- <label class="col-1">&nbsp;</label>  -->
      <div class="col-9 col-m-8">
        <select  class="form-control opt_kota" name="kota" required>
          <option value="ALL">ALL</option>
          <?php 
            foreach($kota as $s)
            {
              echo("<option value='".$s->kota."'>".$s->kota."</option>");
            }       
          ?>
        </select>
      </div>
    </div>
    
    <div class="row" align="center">
      <div class="col-12 col-m-12">
        <input type = "submit" name="btnExcel" value="EXCEL"/>
      </div>
    </div>
  </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>
</div> 