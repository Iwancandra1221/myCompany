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
    width:40%;
  }
  .row-input {
    width:60%;
  }
  .row-input {
    color: black;
  }
</style>
  <div class="container">
    <div class="page-title"><?php echo(strtoupper($title));?></div>
     <!-- container here -->
    <?php 
      echo form_open("LaporanEkspedisiPabrik/Preview", array("target"=>"_blank")); 
    ?>
         <div class="form-container">
            <div class="row">
               <div class="row-label" align="right">
                  Tanggal Awal (DD)
               </div>
               <div class="row-input">
                  <input type = "number" name = "dd" value = "<?php echo(date('d'));?>"/>
               </div>
            </div>
            <div class="row">
               <div class="row-label" align="right">
                  Tanggal Akhir (DD)
               </div>
               <div class="row-input">
                  <input type = "number" name = "ddto" value = "<?php echo(date('d'));?>"/>
               </div>
            </div>
            <div class="row">
               <div class="row-label" align="right">
                  Bulan  (MM)  
               </div>
               <div class="row-input">
                  <!-- <input type = "number" name = "mm" /> -->
                <select id="mm" name="mm">
                  <?php 
                    foreach($months as $m)
                    {
                      echo("<option value='".$m->month."'".(($m->month==date('m'))?" selected":"").">".$m->month_name."</option>");
                    }
                  ?>
                </select>
               </div>
            </div>
            <div class="row">
               <div class="row-label" align="right">
                  Tahun (YYYY)
               </div>
               <div class="row-input">
                  <input type = "number" name = "yyyy" value = "<?php echo(date('Y'));?>"/>
               </div>
            </div>
            <div class="row">
               <div class="row-label" align="right">
                  CABANG (CBG)
               </div>
               <div class="row-input">
                  <select name="cbg">
                <?php 
                  foreach ($branches as $b)
                  {
                      echo ('<option value="'.$b->branch_id.'">'.$b->branch_name.'</option>');
                  }
                  echo ('<option value="ALL">ALL</option>');
                ?>
                  </select>
               </div>
            </div>
            <div class="row">
               <div class="row-label" align="right">
                  Email
               </div>
               <div class="row-input">
                  <select name="email">
                    <option value="N">Tampilkan Saja</option>
                    <option value="Y">Tampilkan dan Email</option>
                  </select>
               </div>
            </div>
            <div class="row">
              <div class="row-label">
                &nbsp;
              </div>
              <div class="row-input">
                <input type="checkbox" name="chkKhususDepo" id="chkKhususDepo" value="1">
                <label for="chkKhususDepo" style="color:white;">Khusus Alamat Depo</label>
              </div>
            </div>
            <div class="row" align="center" style="padding-top:20px;">
               <input type = "submit" name="btnPreview" value="PROSES LAPORAN EKSPEDISI PABRIK" value="PROSES"/>
               <input type = "submit" name="btnExcel" value="EXCEL"/>
            </div>
         </div>
      <!-- </form> -->
      <?php echo form_close(); ?>
    </div> <!-- /container -->