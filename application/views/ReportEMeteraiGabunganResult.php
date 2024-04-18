<script>
  $(document).ready(function() {
    $('#TableSettlementOnlyStamp').DataTable({
      "pageLength": 10
    });
    $('#TableSettlementOnlyNotStamp').DataTable({
      "pageLength": 10
    });
    $('#TableSettlementNotStamp').DataTable({
      "pageLength": 10
    });
    $('#TableSettlementStamp').DataTable({
      "pageLength": 10
    });
    $('#TableBothNotStamp').DataTable({
      "pageLength": 5
    });
    $('#TableBothStamp').DataTable({
      "pageLength": 25  
    });
    $('#TableBhaktiOnlyStamp').DataTable({
      "pageLength": 5
    });
    $('#TableBhaktiOnlyNotStamp').DataTable({
      "pageLength": 5
    });
    $("#flash-msg").delay(1200).fadeOut("slow");

    // $(".btnCancel").hide();
    // $(".btnStamp").hide();
    // $(".btnUnstamp").hide();
  } );

  // function checkDocument(SN="", jenisDoc="", noDoc="", dbId=0)
  // {
  //   $(".loading").show();
  //   var csrf_bit = $("input[name=csrf_bit]").val();
  //   $.post("<?php echo site_url('ReportPenggunaanEMeterai/CheckDocument'); ?>", {
  //     DocType     : jenisDoc,
  //     DocNo       : noDoc,
  //     DbId        : dbId,
  //     csrf_bit    : csrf_bit
  //   }, function(data){

  //     if (data.result=="success") {
  //       var meterai = data.stamp;
  //       $("#meterai_"+SN).html("<b>"+meterai.SERIAL_NUMBER+"</b><br>EmailedDate: "+meterai.EMAILED_DATE); 
  //       if (SN==meterai.SERIAL_NUMBER) {
  //         $("#btnUnstamp_"+SN).hide();
  //         $("#btnCancel_"+SN).hide();
  //       } else {
  //         $("#btnUnstamp_"+SN).show();
  //         $("#btnCancel_"+SN).show();
  //       }
  //       $(".loading").hide();
  //       return true;
  //     } else {
  //       $(".loading").hide();
  //       alert("GAGAL: ");
  //       return false;
  //     }
  //   },'json',errorAjax);  
  // }

  // function cancelMeterai(SN="", jenisDoc="", noDoc="", dbId=0)
  // { 
  //   var cancelNote = confirm("MASUKKAN ALASAN BATAL");

  //   $(".loading").show();
  //   var csrf_bit = $("input[name=csrf_bit]").val();
  //   $.post("<?php echo site_url('ReportPenggunaanEMeterai/CancelMeterai'); ?>", {
  //     SN          : SN,
  //     DocType     : jenisDoc,
  //     DocNo       : noDoc,
  //     DbId        : dbId,
  //     CancelNote  : cancelNote,
  //     csrf_bit    : csrf_bit
  //   }, function(data){

  //     if (data.result=="success") {
  //       $("#cancel_"+SN).html(data.sn);    
  //       $(".loading").hide();
  //       return true;
  //     } else {
  //       $(".loading").hide();
  //       alert("GAGAL: ");
  //       return false;
  //     }
  //   },'json',errorAjax);  
  // }

  // function setNotStamp(SN="", jenisDoc="", noDoc="", dbId=0)
  // {
  //   $(".loading").show();
  //   var csrf_bit = $("input[name=csrf_bit]").val();
  //   $.post("<?php echo site_url('ReportPenggunaanEMeterai/SetNotStamp'); ?>", {
  //     SN          : SN,
  //     DocType     : jenisDoc,
  //     DocNo       : noDoc,
  //     DbId        : dbId,
  //     csrf_bit    : csrf_bit
  //   }, function(data){

  //     if (data.result=="success") {
  //       $("#unstamp_"+SN).html("SUKSES");    
  //       $(".loading").hide();
  //       return true;
  //     } else {
  //       $(".loading").hide();
  //       // alert("GAGAL: ");
  //       return false;
  //     }
  //   },'json',errorAjax);  
  // }

  // function setStamp(SN="", jenisDoc="", noDoc="", dbId=0)
  // {
  //   $(".loading").show();
  //   var csrf_bit = $("input[name=csrf_bit]").val();
  //   $.post("<?php echo site_url('ReportPenggunaanEMeterai/SetStamp'); ?>", {
  //     SN          : SN,
  //     DocType     : jenisDoc,
  //     DocNo       : noDoc,
  //     DbId        : dbId,
  //     csrf_bit    : csrf_bit
  //   }, function(data){

  //     if (data.result=="success") {
  //       $("#unstamp_"+SN).html("SUKSES");    
  //       $(".loading").hide();
  //       return true;
  //     } else {
  //       $(".loading").hide();
  //       // alert("GAGAL: ");
  //       return false;
  //     }
  //   },'json',errorAjax);  
  // }
</script>

  <div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
    <div style="padding: 5px;">
    <?php
        if (isset($_GET['insertsuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Inserted <strong>Successfully !</strong></div>";
        }

        if (isset($_GET['updatesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Updated <strong>Successfully !</strong></div>";
        }

        if (isset($_GET['deletesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Removed <strong>Successfully !</strong></div>";
        }
    ?>

    <?php
        if (isset($_GET['inserterror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Insert Data Error ! ".$_GET['inserterror']."</strong></div>";
        }

        if (isset($_GET['updateerror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Update Data Error ! ".$_GET['updateerror']."</strong></div>";
        }

        if (isset($_GET['deleteerror'])) {
          echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Delete Data Error !</strong></div>";
        }
    ?>
    </div>
  </div>
  <div class="container" style="width:1500px!important;">
    <h3>SUMMARY SETTLEMENT PERIODE: <b><?php echo($BULAN." / ".$TAHUN);?></b></h3>
	<a href="ExcelDataGabungan?th=<?php echo $TAHUN ?>&bl=<?php echo $BULAN ?>" target="_blank" style="float:right"><button>Download Excel</button></a>
    <?php 
      $TOTAL_STAMP = count($SETTLEMENTSTAMP)+count($SETTLEMENTONLYSTAMP)+count($BOTHSTAMP);
      $TOTAL_NOTSTAMP = count($SETTLEMENTNOTSTAMP)+count($SETTLEMENTONLYNOTSTAMP)+count($BOTHNOTSTAMP);
      $TOTAL_METERAI = $TOTAL_STAMP+$TOTAL_NOTSTAMP;
    ?>
    <h3>TOTAL METERAI : <b><?php echo(number_format($TOTAL_METERAI));?></b></h3>
    <div style="width:50%;float:left;">
      TOTAL SETTLEMENT ONLY SUDAH STAMP: <b><?php echo(number_format(count($SETTLEMENTONLYSTAMP)));?></b><br>
      TOTAL SETTLEMENT SUDAH STAMP - BHAKTI BELUM: <b><?php echo(number_format(count($SETTLEMENTSTAMP)));?></b><br>
      TOTAL SETTLEMENT & BHAKTI SUDAH STAMP: <b><?php echo(number_format(count($BOTHSTAMP)));?></b><br>
      ==========================================================================================<br>
      TOTAL SETTLEMENT SUDAH STAMP: <b><?php echo(number_format($TOTAL_STAMP));?></b><br>
    </div>
    <div style="width:50%;float:left;">
      TOTAL SETTLEMENT ONLY BELUM STAMP: <b><?php echo(number_format(count($SETTLEMENTONLYNOTSTAMP)));?></b><br>
      TOTAL SETTLEMENT BELUM STAMP - BHAKTI SUDAH: <b><?php echo(number_format(count($SETTLEMENTNOTSTAMP)));?></b><br>
      TOTAL SETTLEMENT & BHAKTI BELUM STAMP: <b><?php echo(number_format(count($BOTHNOTSTAMP)));?></b><br>
      ==========================================================================================<br>
      TOTAL SETTLEMENT BELUM STAMP: <b><?php echo(number_format($TOTAL_NOTSTAMP));?></b><br>
    </div>
  </div>
  <div style="height:20px;clear:both;"></div>
  <div class="container" style="width:1500px!important;">
    <h3>SUMMARY BHAKTI PERIODE: <b><?php echo($BULAN." / ".$TAHUN);?></b></h3>
    <?php 
      $TOTAL_STAMP = count($BHAKTIONLYSTAMP)+count($SETTLEMENTNOTSTAMP)+count($BOTHSTAMP);
      $TOTAL_NOTSTAMP = count($BHAKTIONLYNOTSTAMP)+count($SETTLEMENTSTAMP)+count($BOTHNOTSTAMP);
      $TOTAL_METERAI = $TOTAL_STAMP+$TOTAL_NOTSTAMP;
    ?>
    <h3>TOTAL METERAI : <b><?php echo(number_format($TOTAL_METERAI));?></b></h3>
    <div style="width:50%;float:left;">
      <!-- TOTAL SUDAH STAMP: <b><?php echo(number_format(count($SETTLEMENTONLYSTAMP)));?></b><br> -->
      <!-- ==========================================================================================<br> -->
      TOTAL BHAKTI SUDAH STAMP: <b><?php echo(number_format($TOTAL_STAMP));?></b><br>
    </div>
    <div style="width:50%;float:left;">
      <!-- TOTAL SETTLEMENT ONLY BELUM STAMP: <b><?php echo(number_format(count($SETTLEMENTONLYNOTSTAMP)));?></b><br> -->
      <!-- TOTAL SETTLEMENT BELUM STAMP - BHAKTI SUDAH: <b><?php echo(number_format(count($SETTLEMENTNOTSTAMP)));?></b><br> -->
      <!-- TOTAL SETTLEMENT & BHAKTI BELUM STAMP: <b><?php echo(number_format(count($BOTHNOTSTAMP)));?></b><br> -->
      <!-- ==========================================================================================<br> -->
      TOTAL BHAKTI BELUM STAMP: <b><?php echo(number_format($TOTAL_NOTSTAMP));?></b><br>
    </div>
  </div>
  <div style="height:20px;clear:both;"></div>
  <div class="container" style="width:1500px!important;">
    <h3>HANYA ADA DI SETTLEMENT SUDAH STAMP</h3>
    <table id="TableSettlementOnlyStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NamaFile</th>";
        echo "<th>Status</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $SETTLEMENTONLYSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td><?php echo($no)?></td>
           <td><?php echo($d->EMeterai_SN)?></td>
           <td><?php echo(date("d-M-Y", strtotime($d->Settlement_Date)))?></td>
           <td><?php echo($d->Settlement_Desc)?></td>
           <td><?php echo($d->Settlement_File)?></td>
           <td><?php echo($d->Settlement_Status)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>
    </table>
    <h3>HANYA ADA DI SETTLEMENT BELUM STAMP</h3>
    <table id="TableSettlementOnlyNotStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NamaFile</th>";
        echo "<th>Status</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $SETTLEMENTONLYNOTSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td><?php echo($no)?></td>
           <td><?php echo($d->EMeterai_SN)?></td>
           <td><?php echo(date("d-M-Y", strtotime($d->Settlement_Date)))?></td>
           <td><?php echo($d->Settlement_Desc)?></td>
           <td><?php echo($d->Settlement_File)?></td>
           <td><?php echo($d->Settlement_Status)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>
    </table>

    <h3>SETTLEMENT BELUM STAMP - BHAKTI SUDAH STAMP</h3>
    <table id="TableSettlementNotStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NamaFile</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>NilaiDokumen</th>";
        echo "<th>StampingDate</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $SETTLEMENTNOTSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td><?php echo($no)?></td>
           <td><?php echo($d->EMeterai_SN)?></td>
           <td><?php echo(date("d-M-Y", strtotime($d->Settlement_Date)))?></td>
           <td><?php echo($d->Settlement_Desc)?></td>
           <td><?php echo($d->Settlement_File)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_No)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->NamaLawanTransaksi."<br>".$d->KodeLawanTransaksi."<br>".$d->NPWPLawanTransaksi)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo(number_format($d->Document_Value))?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_StampingDate)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>

    </table>

    <h3>SETTLEMENT SUDAH STAMP - BHAKTI BELUM STAMP</h3>
    <table id="TableSettlementStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NamaFile</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>NilaiDokumen</th>";
        echo "<th>Catatan</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $SETTLEMENTSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td><?php echo($no)?></td>
           <td><?php echo($d->EMeterai_SN)?></td>
           <td><?php echo(date("d-M-Y", strtotime($d->Settlement_Date)))?></td>
           <td><?php echo($d->Settlement_Desc)?></td>
           <td><?php echo($d->Settlement_File)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_No)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->NamaLawanTransaksi."<br>".$d->KodeLawanTransaksi."<br>".$d->NPWPLawanTransaksi)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo(number_format($d->Document_Value))?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->ErrorCode.":".$d->ErrorMessage)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>
    </table>

    <h3>SETTLEMENT & BHAKTI BELUM STAMP</h3>
    <table id="TableBothNotStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NamaFile</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>NilaiDokumen</th>";
        echo "<th>Catatan</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $BOTHNOTSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td><?php echo($no)?></td>
           <td><?php echo($d->EMeterai_SN)?></td>
           <td><?php echo(date("d-M-Y", strtotime($d->Settlement_Date)))?></td>
           <td><?php echo($d->Settlement_Desc)?></td>
           <td><?php echo($d->Settlement_File)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_No)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->NamaLawanTransaksi."<br>".$d->KodeLawanTransaksi."<br>".$d->NPWPLawanTransaksi)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo(number_format($d->Document_Value))?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->ErrorCode.":".$d->ErrorMessage)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>
    </table>

    <h3>SETTLEMENT & BHAKTI SUDAH STAMP</h3>
    <table id="TableBothStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NamaFile</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>TotalDokumen</th>";
        echo "<th>StampingDate</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $BOTHSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td><?php echo($no)?></td>
           <td><?php echo($d->EMeterai_SN)?></td>
           <td><?php echo(date("d-M-Y", strtotime($d->Settlement_Date)))?></td>
           <td><?php echo($d->Settlement_Desc)?></td>
           <td><?php echo($d->Settlement_File)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_No)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->NamaLawanTransaksi."<br>".$d->KodeLawanTransaksi."<br>".$d->NPWPLawanTransaksi)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo(number_format($d->Document_Value))?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_StampingDate)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>
    </table>


    <h3>HANYA ADA DI BHAKTI SUDAH STAMP</h3>
    <table id="TableBhaktiOnlyStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>TotalDokumen</th>";
        echo "<th>StampingDate</th>";
        echo "<th>NamaFile</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $BHAKTIONLYSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($no)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_SN)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_RequestDate)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_Type)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_No)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->NamaLawanTransaksi."<br>".$d->KodeLawanTransaksi."<br>".$d->NPWPLawanTransaksi)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo(number_format($d->Document_Value))?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_StampingDate)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_FileName)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>

    </table>

    <h3>HANYA ADA DI BHAKTI BELUM  STAMP</h3>
    <table id="TableBhaktiOnlyNotStamp" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>TotalDokumen</th>";
        echo "<th>StampingDate</th>";
        echo "<th>NamaFile</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $BHAKTIONLYNOTSTAMP;        
        // for($i=0;$i<count($data);$i++) {
        foreach($data as $d) { 
      ?>
           <tr id="tr_<?php echo($d->EMeterai_SN)?>">
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($no)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_SN)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_RequestDate)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_Type)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_No)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->NamaLawanTransaksi."<br>".$d->KodeLawanTransaksi."<br>".$d->NPWPLawanTransaksi)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo(number_format($d->Document_Value))?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->EMeterai_StampingDate)?></td>
           <td style="color:<?php echo(($d->IsCancelled==1)?'red':'black');?>;"><?php echo($d->Document_FileName)?></td>
           </tr>
      <?php 
          $no += 1;
      }
        echo "</tbody>"; 
      ?>

    </table>
  </div>

<?php form_open(); ?>
<?php form_close(); ?>