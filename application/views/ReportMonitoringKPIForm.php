<script>
    $(document).ready(function() {
      var err = "<?php echo($err); ?>";

      if (err!="") {
        alert(err);
      }

      $("#Tahun").change(function() {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();
      });

      $('#Bulan').on('change', function() {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();
      });

    } );
</script>

<style type="text/css">
  input { color:black!important;}
</style>

<div class="container">
  <div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php 
    $DEST = "ReportKPI/Proses/MonitoringAchievement";
    echo form_open($DEST, array("target"=>"_blank")) 
  ?>
    <div class="form-container">
      <div class="row LAPORAN" style="">
         <div class="col-3 col-m-4" align="right">
            Laporan
         </div>
         <div class="col-9 col-m-8">
            <input type="text" name="Laporan" id="Laporan" value="MONITORING ACHIEVEMENT KPI" style="width:100%!important;" disabled>
         </div>
      </div>
      <div class="row" style="">
         <div class="col-3 col-m-4" align="right">
            Kategori
         </div>
         <div class="col-9 col-m-8">
            <select name="Category" id="Category" style="width:100%!important;">
          <?php for($c=0;$c<count($categories);$c++) { ?>
            <option value="<?php echo($categories[$c]["Kategori"]);?>"><?php echo($categories[$c]["Kategori"]);?></option>
          <?php } ?>
            </select>
         </div>
      </div>
      <div class="row PERIODE" style="">
         <div class="col-3 col-m-4" align="right">
            Tahun
         </div>
         <div class="col-9 col-m-8">
            <input type="text" name="Tahun" id="Tahun" value="<?php echo(date('Y'));?>" style="width:100%!important;" >
         </div>
      </div>
      <div class="row PERIODE" style="">
         <div class="col-3 col-m-4" align="right">
            Bulan
         </div>
         <div class="col-9 col-m-8">
            <select name="Bulan" id="Bulan" style="width:100%!important;">
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
      <div class="row" align="center">
        <div class="col-12 col-m-12">
          <!-- <input type = "submit" name="btnPreview" value="PREVIEW"/> -->
          <!-- <input type = "submit" name="btnPdf" value="PREVIEW PDF"/> -->
          <input type = "submit" name="btnExcel" value="EXCEL"/>
        </div>
      </div>
    </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>
</div> 