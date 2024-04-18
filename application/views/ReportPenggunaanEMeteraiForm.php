<script>
    function number_format(number, decimals, decPoint, thousandsSep){
      decimals = decimals || 0;
      number = parseFloat(number);

      if(!decPoint || !thousandsSep){
          decPoint = '.';
          thousandsSep = ',';
      }

      var roundedNumber = Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';
      // add zeros to decimalString if number of decimals indicates it
      roundedNumber = (1 > number && -1 < number && roundedNumber.length <= decimals)
              ? Array(decimals - roundedNumber.length + 1).join("0") + roundedNumber
              : roundedNumber;
      var numbersString = decimals ? roundedNumber.slice(0, decimals * -1) : roundedNumber.slice(0);
      var checknull = parseInt(numbersString) || 0;
  
      // check if the value is less than one to prepend a 0
      numbersString = (checknull == 0) ? "0": numbersString;
      var decimalsString = decimals ? roundedNumber.slice(decimals * -1) : '';
      
      var formattedNumber = "";
      while(numbersString.length > 3){
          formattedNumber = thousandsSep + numbersString.slice(-3) + formattedNumber;
          numbersString = numbersString.slice(0,-3);
      }

      return (number < 0 ? '-' : '') + numbersString + formattedNumber + (decimalsString ? (decPoint + decimalsString) : '');
    }  

    $(document).ready(function() {
      $('#TblLog').DataTable({
        "pageLength": 25
      });
      SearchAgain();
      SearchAgain("SETTLEMENT");

      function SearchAgain(src="BHAKTI") {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();

        if (src=="BHAKTI") {
          $("#TblLogBody").html("");
        } else {
          $("#TblSettlementBody").html("");
        }

        var csrf_bit=$("input[name=csrf_bit]").val();
        $(".loading").show();
            $.post("<?php echo(site_url('ReportPenggunaanEMeterai/LoadLogs')); ?>", {   
              src         : src,
              th          : th,
              bl          : bl,
              csrf_bit    : csrf_bit
            }, function(hasil){
          var NO=0;
          var BRS = "";
          if (hasil.result == "sukses") {
            
            var List = hasil.list;
            for(var i=0;i<List.length;i++) {

              NO++;
              var logs = List[i];

              if (src=="BHAKTI") {
                BRS = '<tr id="tr_'+logs.LogID+'">';
                BRS+= ' <td>'+((logs.ModifiedDate==null)?logs.CreatedDate:logs.ModifiedDate)+'</td>';
                BRS+= ' <td>'+logs.Kode_Lokasi+'</td>';
                BRS+= ' <td>'+logs.Tahun+'</td>';
                BRS+= ' <td>'+logs.Bulan+'</td>';
                BRS+= ' <td>'+((logs.ModifiedDate==null)?logs.CreatedBy:logs.ModifiedBy)+'</td>';
                BRS+= ' <td>'+logs.TotalRecordAlreadyLocked+'</td>';
                BRS+= ' <td>'+logs.TotalRecordInserted+'</td>';
                BRS+= ' <td>'+logs.TotalRecordModifiedAndLocked+'</td>';
                BRS+= ' <td>'+logs.TotalRecordModified+'</td>';
                BRS+= ' <td>'+logs.TotalRecord+'</td>';
                BRS+= '</tr>';
                $("#TblLogBody").append(BRS);

              } else {

                BRS = '<tr id="tr_'+logs.LogID+'">';
                BRS+= ' <td>'+((logs.ModifiedDate==null)?logs.CreatedDate:logs.ModifiedDate)+'</td>';
                BRS+= ' <td>'+logs.Kode_Lokasi+'</td>';
                BRS+= ' <td>'+logs.Tahun+'</td>';
                BRS+= ' <td>'+logs.Bulan+'</td>';
                BRS+= ' <td>'+((logs.ModifiedDate==null)?logs.CreatedBy:logs.ModifiedBy)+'</td>';
                BRS+= ' <td>'+logs.TotalStamp+'</td>';
                BRS+= ' <td>'+logs.TotalNotStamp+'</td>';
                BRS+= ' <td>'+logs.TotalRecord+'</td>';
                BRS+= ' <td><a href="ReportPenggunaanEMeterai/PreviewDataGabungan?th='+th+'&bl='+bl+'" target="_blank"><button>VIEW</button></a></td>';
                BRS+= ' <td><a href="ReportPenggunaanEMeterai/PreviewDataGabunganPajakku?th='+th+'&bl='+bl+'" target="_blank"><button>PAJAKKU</button></a></td>';
                BRS+= '</tr>';
                $("#TblSettlementBody").append(BRS);
              }
            }
            $(".loading").hide();
          } else {
            $(".loading").hide();
          }
        },'json',errorAjax);
      }  

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

      $("#btnRefresh").click(function(){
        SearchAgain();
        SearchAgain("SETTLEMENT");
      });

      $("#Tahun").change(function() {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();
        SearchAgain();

        $("#STahun").val($(this).val());
        SearchAgain("SETTLEMENT");
        //CheckReport(th, bl);
      });

      $('#Bulan').on('change', function() {
        var th = $("#Tahun").val();
        var bl = $("#Bulan").val();
        SearchAgain();

        $("#SBulan").val($(this).val());
        SearchAgain("SETTLEMENT");
      });

    } );
</script>

<style type="text/css">
</style>

<div class="container">
  <div class="page-title"><?php echo(strtoupper($title));?></div>

  <?php 
    $DEST = (($meterai_type=="METERAI KOMPUTERISASI")? "ReportPenggunaanEMeterai/Proses/Komputerisasi":"ReportPenggunaanEMeterai/Proses/Elektronik");
    echo form_open($DEST, array("target"=>"_blank")) 
  ?>
    <div class="form-container" style="height:450px;width:600px;">
      <div class="row LAPORAN" style="">
         <div class="col-3 col-m-4">
            Laporan
         </div>
         <div class="col-9 col-m-8">
            <input type="text" name="Laporan" id="Laporan" value="<?php echo($meterai_type);?>" disabled>
         </div>
      </div>
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
            <option value="04">01 - AKHIR BULAN</option>
            <!-- <option value="01">01 - 10</option> -->
            <!-- <option value="02">11 - 20</option> -->
            <!-- <option value="03">21 - AKHIR BULAN</option> -->
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
      <div class="row">
        <div class="col-3 col-m-4">Jenis Tanggal</div>
        <div class="col-5 col-m-6">
          <select name="DateOption" id="DateOption">
          <?php if ($meterai_type=="METERAI KOMPUTERISASI") { ?>
            <option value="TANGGAL STAMPING">TGL PEMBUBUHAN METERAI</option>
          <?php } else { ?>
            <option value="TANGGAL REQUEST">TGL REQUEST S/N METERAI</option>
            <option value="TANGGAL STAMPING">TGL PEMBUBUHAN METERAI</option>
          <?php } ?>
          </select>
        </div>
      </div>
      <div class="row PERIODE">
        <div class="col-3 col-m-4">DATABASE</div>
        <div class="col-5 col-m-6">
          <select name="Database" id="Database">
          <?php foreach($databases as $d) { ?>
            <option value="<?php echo($d->DatabaseId);?>"><?php echo($d->NamaDb);?></option>
          <?php } ?>
          </select>
        </div>
      </div>

      <div class="row" align="center">
        <div class="col-12 col-m-12">
          <input type = "submit" name="btnSend" value="IMPORT DATA METERAI"/>
          <input type = "submit" name="btnPreview" value="PREVIEW"/>
          <input type = "submit" name="btnPdf" value="PREVIEW PDF"/>
          <input type = "submit" name="btnExcel" value="EXCEL"/>
        </div>
      </div>
      <?php if ($userBranch=="JKT") { ?>
      <div class="row" align="center">
        <div class="col-12 col-m-12">
          <input type = "submit" name="btnListMeterai" value="METERAI CABANG "/>
          <!-- <input type = "submit" name="btnImported" value="LIST METERAI GABUNGAN HASIL IMPORT"/> -->
        </div>
      </div>
      <?php } ?>
    </div>
  <?php echo form_close(); ?>
  <?php if ($userBranch=="JKT") { ?>
  <div class="form-container" style="height:550px;width:600px;">
    <h3>Import File Settlement</h3>
    <form method="post" action="ReportPenggunaanEMeterai/ImportSettlement" target="_blank" enctype="multipart/form-data">
      <!-- <a href="Format.xlsx">Download Format</a>  |  -->
      <!-- <a href="index.php">Kembali</a> -->
      <br><br>
      <div class="row PERIODE" style="">
         <div class="col-3 col-m-4">
            Tahun
         </div>
         <div class="col-9 col-m-8">
            <input type="text" name="STahun" id="STahun" value="<?php echo(date('Y'));?>" readonly>
         </div>
      </div>
      <div class="row PERIODE" style="">
         <div class="col-3 col-m-4">
            Bulan
         </div>
         <div class="col-9 col-m-8">
            <input type="text" name="SBulan" id="SBulan" value="<?php echo(date('m'));?>" readonly>
         </div>
      </div>
      <input type="file" name="file">
      <button type='submit' name='import' style='color:black;'>Import</button>
      <!-- <button type="submit" name="preview">Preview</button> -->
    </form>
    <div style="margin-top:20px;">
      <b>SKEMA IMPORT EXCEL SETTLEMENT</b><br>
      - Kolom A: Serial Number<br>
      - Kolom B: Status [STAMP/NOTSTAMP]<br>
      - Kolom C: Deskripsi<br>
      - Kolom D: Nama File<br>
      - Kolom E: Tanggal<br>
      - Kolom G: Nilai Meterai<br><br>
      <i>Jika skema excel berubah, Anda bisa menyesuaikan excel Anda untuk mengimport.<br>
      Kirimkan juga excel settlement yang baru ke <b>itdev.dist@bhakti.co.id</b> 
      agar web disesuaikan dengan skema yang baru</i>   
    </div>
  </div>
  <?php } ?>
  <!-- <div style='clear:both;height:20px;'></div> -->
</div>
<div class="" style="float:left;">
  <div id="history_import" class="container" style="width:1200px!important;">
    <h3>HISTORY IMPORT DATA METERAI</h3>
    <button id="btnRefresh">REFRESH HISTORY</button>
    <table id="TblLog" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th width='15%'>TGL</th>";
        echo "<th width='7%'>SUMBER</th>";
        echo "<th width='7%'>TAHUN</th>";
        echo "<th width='7%'>BULAN</th>";
        echo "<th width='19%'>DIIMPORT OLEH</th>";
        echo "<th width='9%'>TOTAL ALREADY LOCKED</th>";
        echo "<th width='9%'>TOTAL INSERT</th>";
        echo "<th width='9%'>TOTAL MODIFIED<br>+LOCKED</th>";
        echo "<th width='9%'>TOTAL MODIFIED</th>";
        echo "<th width='9%'>TOTAL RECORD</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody id='TblLogBody'>";
        echo "</tbody>"; 
      ?>
    </table>
  </div>
  <div id="history_settlement" class="container" style="width:1200px!important;">
    <h3>HISTORY IMPORT SETTLEMENT</h3>
    <!-- <button id="btnRefresh">REFRESH HISTORY</button> -->
    <table id="TblSettlement" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th width='15%'>TGL</th>";
        echo "<th width='9%'>SUMBER</th>";
        echo "<th width='8%'>TAHUN</th>";
        echo "<th width='8%'>BULAN</th>";
        echo "<th width='20%'>DIIMPORT OLEH</th>";
        echo "<th width='10%'>TOTAL STAMP</th>";
        echo "<th width='10%'>TOTAL NOTSTAMP</th>";
        echo "<th width='10%'>TOTAL RECORD</th>";
        echo "<th width='10%'>VIEW</th>";;
        echo "<th width='10%'>PAJAKKU</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody id='TblSettlementBody'>";
        echo "</tbody>"; 
      ?>
    </table>
  </div>
</div> 