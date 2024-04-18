<script>
  var CheckReport = function(th=0, bl=0) {
    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/GetReport'); ?>", {
      Tahun : th,
      Bulan : bl,
      csrf_bit:csrf_bit
    }, function(data){
      if (data.error != undefined)
      {
        $("#NIK").val("");
        $("#EmployeeName").val("");
        $("#EmployeeLevel").val("");
      }
      else
      {
        $("#NIK").val(data.NIK);
        $("#EmployeeName").val(data.EmployeeName);
        $("#EmployeeLevel").val(data.EmpLevel);
      }
      $(".loading").hide();
    }
    ,'json',errorAjax); 
  };

    $(document).ready(function() {
      $(".TANGGAL").hide();
      var err = "<?php echo($err); ?>";

      if (err!="") {
        alert(err);
      }

      $('#dp1').datepicker({
        format: "mm/dd/yyyy",
        autoclose: true
      });

      $('#dp2').datepicker({
        format: "mm/dd/yyyy",
        autoclose: true
      });

      $('#ReportOption').on('change', function() {
        var opt = $("#ReportOption").val();
        if (opt=="TANGGAL") {
          $(".TANGGAL").show();
          $(".PERIODE").hide();
        } else {
          $(".TANGGAL").hide();
          $(".PERIODE").show();
        }
      });   

      $("#Tahun").change(function() {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();
        CheckReport(th, bl);
      });

      $('#Bulan').on('change', function() {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();
        CheckReport(th, bl);
      });

    } );
</script>

<style type="text/css">
  body { }

  .form-container {
    width: 650px;
    height: 400px;
    /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#deefff+0,98bede+100;Blue+3D+%2310 */
    background: #deefff; /* Old browsers */
    background: -moz-linear-gradient(top,  #deefff 0%, #98bede 100%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top,  #deefff 0%,#98bede 100%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom,  #deefff 0%,#98bede 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#deefff', endColorstr='#98bede',GradientType=0 ); /* IE6-9 */

    border:1px solid blue;
    border-radius:15px;
    padding:15px;
  }
</style>

<div class="container">
  <?php echo form_open("ReportPenggunaanEMeterai/Proses", array("target"=>"_blank")) ?>
    <div class="form-container" style="height:350px;width:500px;">
      <div class="row">
        <div class="col-3 col-m-4">Metode Proses</div>
        <div class="col-5 col-m-6">
          <select name="ReportOption" id="ReportOption">
            <option value="PERIODE">PER PERIODE</option>
            <option value="TANGGAL">PER TANGGAL</option>
          </select>
        </div>
      </div>
      <div class="row PERIODE" style="">
         <div class="col-3 col-m-4">
            Tahun
         </div>
         <div class="col-9 col-m-8">
            <input type="text" name="Tahun" id="Tahun" value="<?php echo(date('Y'));?>">
         </div>
      </div>
      <div class="row PERIODE" style="">
         <div class="col-3 col-m-4">
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
      <div class="row PERIODE">
        <div class="col-3 col-m-4">Periode TGL</div>
        <div class="col-5 col-m-6">
          <select name="Periode" id="Periode">
            <option value="01">01 - 10</option>
            <option value="02">11 - 20</option>
            <option value="03">21 - AKHIR BULAN</option>
          </select>
        </div>
      </div>

      <div class="row TANGGAL">
        <div class="col-3 col-m-4">Tanggal Awal</div>
        <div class="col-5 col-m-6 date">
          <input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1">
        </div>
        <div class="col-1 col-m-2">
          <div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
        </div>
      </div>
      <div class="row TANGGAL">
        <div class="col-3 col-m-4">Tanggal Akhir</div>
        <div class="col-5 col-m-6 date">
          <input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2">
        </div>
        <div class="col-1 col-m-2">
          <div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
        </div>
      </div>
      <div class="row" align="center">
        <div class="col-12 col-m-12">
          <input type = "submit" name="btnPreview" value="PREVIEW"/>
          <input type = "submit" name="btnPdf" value="PREVIEW PDF"/>
        </div>
      </div>
    </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>
  <div>
  </div>
</div> 