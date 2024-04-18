<script>
    /*var CheckData = function() {
      var th = $("#Tahun").val();
      var bl = $("#Bulan").val();
      $("#LogOmzetNasional").hide();     
      $('tbody').html("");

      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo site_url('ReportOmzet/SummaryOmzetNasional'); ?>", {
        th   : th,
        bl   : bl,
        csrf_bit:csrf_bit
      }, function(data){
        if (data.result=="sukses") {
          if (data.error=="") {

            var summary = data.data;
            var x = "";
            for(var i=0; i<summary.length; i++)
            {
              $('<tr>'
                +'<td>'+summary[i]["Wilayah"]+'</td>'
                +'<td>'+summary[i]["CreatedBy"]+'</td>'
                +'<td>'+summary[i]["CreatedDate"]+'</td>'
                +'<td style="display:none;">'+((summary[i]["LockedBy"]==1)?"LOCKED"+"<br>"+summary[i]["LockedBy"]+'<br>'+summary[i]["LockedDate"]:"NOT LOCKED")+'</td>'
                +'<td style="display:none;"><input type="button" name="locked'+i+'" kat="'+summary[i]["KategoriBrg"]+'" wil="'+summary[i]["Wilayah"]+'" value="'+((summary[i]["LockedBy"]==1)?"UNLOCK":"LOCK")+'"/></td></tr>').appendTo($('tbody'));              
            }   
            $("#LogOmzetNasional").show();         
          }
        }
        $(".loading").hide();
      }
      ,'json',errorAjax);
    }*/

    $(document).ready(function() {
      /*$("#LogOmzetNasional").hide();
      var err = "<?php echo($err); ?>";
      if (err!="") {
        alert(err);
      }
      CheckData();

      $("#Tahun").change(function() {
        CheckData();
      });

      $('#Bulan').on('change', function() {
        CheckData();
      });*/

    } );
</script>

<style type="text/css">
  th, td { border:1px solid #000; padding: 2px 10px 2px 10px; }
</style>

<div class="">
  <?php 
    echo form_open($formURL, array("target"=>"_blank")) 
  ?>
  <div class="form-container" style="height:150px;width:500px;">
   <div class="row PERIODE" style="display:none;">
       <div class="col-3 col-m-4">
          Tahun
       </div>
       <div class="col-9 col-m-8">
          <input type="text" name="Tahun" id="Tahun" value="<?php echo(date('Y'));?>">
       </div>
    </div>
    <div class="row PERIODE" style="display:none;">
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
    <div class="row" align="center">
      <div class="col-12 col-m-12">
        <input type = "submit" name="btnPreview" value="PREVIEW"/>
        <?php if ($btnPDF==1) { ?>
        <input type = "submit" name="btnPdf" value="PREVIEW PDF"/>
        <?php } ?>
        <?php if ($btnExcel==1) { ?>
        <input type = "submit" name="btnExcel" value="EXCEL"/>
        <?php } ?>
      </div>
    </div>
  </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>
  <?php if ($opt=="OMZET NASIONAL") { ?>
  <div class="form-container" id="LogOmzetNasional" style="height:350px;width:95%;overflow-y:scroll;background-color:#fff!important;">
    <table>
      <thead>
        <tr>
          <th width="25%">Wilayah</th>
          <th width="40%" colspan="2">Proses</th>
          <th width="15%" style="display:none;">Locked</th>
          <th width="10%" style="display:none;">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
  <?php } ?>
</div> 