<script>
  $(document).ready(function() {
    $('#TableNotStamped').DataTable({
      "pageLength": 5
    });
    $('#TableCancelled').DataTable({
      "pageLength": 5
    });
    $('#TableStamped').DataTable({
      "pageLength": 25
    });
    $("#flash-msg").delay(1200).fadeOut("slow");

    hideAllButtons();
    // $(".btnCancel").hide();
    // $(".btnStamp").hide();
    // $(".btnUnstamp").hide();
    // $(".btnChangeDoc").hide();
    // $(".btnRemove").hide();
  });

  function hideAllButtons()
  {
    $(".btnCancel").hide();
    $(".btnStamp").hide();
    $(".btnUnstamp").hide();
    $(".btnChangeDoc").hide();
    $(".btnRemove").hide();
  }

  function checkDocument(currentStatus="", SN="", jenisDoc="", noDoc="", dbId=0)
  {
    // alert(currentStatus);
    var btnCheck = "<button id='btnCheck_"+SN+"' class='btnCheck' onclick='checkDocument(''"+currentStatus+"'', ''"+SN+"'', ''"+jenisDoc+"'',''"+noDoc+"'', "+dbId+")>Cek</button>";    
    hideAllButtons();  

    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/CheckDocument'); ?>", {
      DocType     : jenisDoc,
      DocNo       : noDoc,
      DbId        : dbId,
      csrf_bit    : csrf_bit
    }, function(data){
      // alert(data.result);
      if (data.result=="success") {
        var meterai = data.stamp;
        if (meterai.SERIAL_NUMBER=="") {

          if (currentStatus=="NOTSTAMP") {
            //DI DOKUMEN NO METERAI MASIH KOSONG (Belum Dibubuhkan)
            //METERAI BELUM STAMP. 
            
            //1. Diset SUDAH STAMP, jika settlement STAMP dan No Dokumen SAMa
            $("#btnStamp_"+SN).show(); 

            //2. DIBATALKAN jika settlement STAMP dan No Dokumen Berbeda
            $("#btnCancel_"+SN).show();

            //3. GANTI DOKUMEN, jika settlement NOTSTAMP dan Dokumen yang tadinya mau distamp tidak jadi dipakaikan meterai
            $("#btnChangeDoc_"+SN).show();

            //4. HAPUS, jika di settlement tidak ada
            $("#btnRemove_"+SN).show();

          } else if (currentStatus=="STAMP") {


            $("#btnCancel_"+SN).show();
            $("#btnChangeDoc_"+SN).show();
            $("#btnRemove_"+SN).show();

          } else {
            $("#btnStamp_"+SN).show();
            $("#btnUnstamp_"+SN).show();
            $("#btnChangeDoc_"+SN).show();
            $("#btnRemove_"+SN).show();            
          }

        } else if (SN==meterai.SERIAL_NUMBER) {
          $("#meterai_"+SN).html("<b style='color:#288a3b;'>"+meterai.SERIAL_NUMBER+"</b><br>EmailedDate: "+meterai.EMAILED_DATE+"<br>"+btnCheck); 
          if (currentStatus=="NOTSTAMP") {
            $("#btnCancel_"+SN).show();
            $("#btnStamp_"+SN).show(); 
            $("#btnChangeDoc_"+SN).show();
            $("#btnRemove_"+SN).show();

          } else if (currentStatus=="STAMP") {
            $("#btnCancel_"+SN).show();
            $("#btnChangeDoc_"+SN).show();
            $("#btnRemove_"+SN).show();

          } else {
            $("#btnStamp_"+SN).show();
            $("#btnUnstamp_"+SN).show();
            $("#btnChangeDoc_"+SN).show();
            $("#btnRemove_"+SN).show();                        
          }

        } else {

          $("#meterai_"+SN).html("<b style='color:red;'>"+meterai.SERIAL_NUMBER+"</b><br>EmailedDate: "+meterai.EMAILED_DATE+"<br>"+btnCheck); 
          if (currentStatus=="NOTSTAMP") {
            //NO METERAI di dokumen BEDA dengan No Meterai yang sedang dicek
            //METERAI BELUM STAMP

            //1. set BATAL jika settlement sudah STAMP
            $("#btnCancel_"+SN).show();
            // $("#btnStamp_"+SN).show();

            //2. bisa DIPAKAI UNTUK DOKUMEN LAIN, jika di settlement NOT STAMP 
            $("#btnChangeDoc_"+SN).show();

            //3. bisa dihapus jika di sellement tidak ada.
            $("#btnRemove_"+SN).show();

          } else if (currentStatus=="STAMP") {
            //NO METERAI di dokumen BEDA dengan No Meterai yang sedang dicek
            //METERAI STAMP
            //jika status Bhakti STAMP--> kemungkinan besar sudah diterima pihak dealer/konsumen

            //1. set BATAL jika di settlement SUDAH STAMP
            $("#btnCancel_"+SN).show();

            // bisa DIPAKAI UNTUK DOKUMEN LAIN jika di settlement NOT STAMP
            $("#btnChangeDoc_"+SN).show();

            //2. bisa diHAPUS jika di settlement tidak ada
            $("#btnRemove_"+SN).show();

          } else {
            //NO METERAI di dokumen BEDA dengan No Meterai yang sedang dicek
            //METERAI BATAL

            // $("#btnStamp_"+SN).show();
            // $("#btnUnstamp_"+SN).show();

            //1. bisa digunakan untuk Dokumen Lain, jika settlement NOT STAMP 
            $("#btnChangeDoc_"+SN).show();

            //2. bisa dihapus jika di settlement tidak ada
            $("#btnRemove_"+SN).show();            
          }

        }
        $(".loading").hide();
        return true;

      } else {
        $("#meterai_"+SN).html("<b style='color:red;'>Dokumen Tidak Ditemukan</b><br>"+"<br>"+btnCheck); 
        // alert(data.error);
        $("#btnCancel_"+SN).show();
        $("#btnStamp_"+SN).show(); 
        $("#btnChangeDoc_"+SN).show();
        $("#btnRemove_"+SN).show();   
        $(".loading").hide();
        return false;

      }
    },'json',errorAjax);
  }

 
  function cancelMeterai(SN="", jenisDoc="", noDoc="", dbId=0)
  { 
    var cancelNote = prompt("MASUKKAN ALASAN BATAL");
    // var cancelNote = "TIDAK BERHASIL STAMP";

    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/CancelMeterai'); ?>", {
      SN          : SN,
      DocType     : jenisDoc,
      DocNo       : noDoc,
      DbId        : dbId,
      CancelNote  : cancelNote,
      csrf_bit    : csrf_bit
    }, function(data){

      if (data.result=="success") {
        $("#cancel_"+SN).html("SUKSES");    
        $(".loading").hide();
        return true;
      } else {
        $(".loading").hide();
        alert("GAGAL:"+data.stamp);
        return false;
      }
    },'json',errorAjax);  
  }

  function setNotStamp(SN="", jenisDoc="", noDoc="", dbId=0)
  {
    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/SetNotStamp'); ?>", {
      SN          : SN,
      DocType     : jenisDoc,
      DocNo       : noDoc,
      DbId        : dbId,
      csrf_bit    : csrf_bit
    }, function(data){

      if (data.result=="success") {
        $("#unstamp_"+SN).html("SUKSES");    
        $(".loading").hide();
        return true;
      } else {
        $(".loading").hide();
        // alert("GAGAL: ");
        return false;
      }
    },'json',errorAjax);  
  }

  function setStamp(SN="", jenisDoc="", noDoc="", dbId=0)
  {
    var stampDate = prompt("MASUKKAN TGL STAMP [YYYY-MM-DD]");
    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/SetStamp'); ?>", {
      SN          : SN,
      DocType     : jenisDoc,
      DocNo       : noDoc,
      DbId        : dbId,
      StampDate   : stampDate,
      csrf_bit    : csrf_bit
    }, function(data){

      if (data.result=="success") {
        $("#stamp_"+SN).html("SUKSES");    
        $(".loading").hide();
        return true;
      } else {
        $(".loading").hide();
        // alert("GAGAL: ");
        return false;
      }
    },'json',errorAjax);  
  }
  
  function changeDoc(SN="", jenisDoc="", noDoc="", dbId=0)
  { 
    var cancelNote = "GANTI DOKUMEN";
    // var cancelNote = "TIDAK BERHASIL STAMP";

    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/ChangeDoc'); ?>", {
      SN          : SN,
      DocType     : jenisDoc,
      DocNo       : noDoc,
      DbId        : dbId,
      CancelNote  : cancelNote,
      csrf_bit    : csrf_bit
    }, function(data){

      if (data.result=="success") {
        $("#changedoc_"+SN).html("SUKSES");    
        $(".loading").hide();
        return true;
      } else {
        $(".loading").hide();
        alert("GAGAL:"+data.error);
        return false;
      }
    },'json',errorAjax);  
  }

  function removeMeterai(SN="", jenisDoc="", noDoc="", dbId=0)
  { 
    var cancelNote = "TIDAK ADA DI SETTLEMENT";
    // var cancelNote = "TIDAK BERHASIL STAMP";

    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('ReportPenggunaanEMeterai/Remove'); ?>", {
      SN          : SN,
      DocType     : jenisDoc,
      DocNo       : noDoc,
      DbId        : dbId,
      CancelNote  : cancelNote,
      csrf_bit    : csrf_bit
    }, function(data){

      if (data.result=="success") {
        $("#remove_"+SN).html("SUKSES");    
        $(".loading").hide();
        return true;
      } else {
        $(".loading").hide();
        alert("GAGAL:"+data.error);
        return false;
      }
    },'json',errorAjax);  
  }

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
    <h3>METERAI BELUM DISTAMP <font color="#80ff45" style="background-color:#1f0887;"><?php echo($branch);?></font></h3>
    <div>
      Catatan:<br>
      - tombol SUDAH STAMP: meterai ada di settlement, status SUDAH STAMP. No Dokumen di settlement = Bhakti<br>
      - tombol BATAL : meterai ada di settlement, status sudah STAMP. Dokumen Bhakti sudah terstamp dengan nomor berbeda.<br>
      - tombol GANTI DOKUMEN : meterai ada di settlement, status BELUM STAMP. Dokumen Bhakti sudah terstamp dengan nomor berbeda.<br>
      - tombol HAPUS : meterai tidak ada di settlement. Dokumen Bhakti belum terstamp dan akan diproses ulang dengan meminta serial number baru.<br>
    </div>
    <table id="TableNotStamped" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        // if($_SESSION["can_delete"] == 1)
        echo "<th>TotalDokumen</th>";
        echo "<th>CekDokumen</th>";
        echo "<th>SetStamp</th>";
        echo "<th>SetBatal</th>";
        echo "<th>Ganti<br>Dokumen</th>";
        echo "<th>TidakAda<br>DiSettlement</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $meteraiNotStamped;
        for($i=0;$i<count($data);$i++) { 
      ?>
           <tr id="tr_<?php echo($data[$i]["SERIAL_NUMBER"])?>">
           <td><?php echo($no)?></td>
           <td><?php echo($data[$i]["SERIAL_NUMBER"])?></td>
           <td><?php echo($data[$i]["CDATE"])?></td>
           <td><?php echo($data[$i]["TYPE_TRANS"])?></td>
           <td><?php echo($data[$i]["NO_BUKTI"]."<br>".$data[$i]["KET"])?></td>
           <td><?php echo($data[$i]["NM_PLG"]."<br>".$data[$i]["KD_PLG"]."<br>".$data[$i]["NPWP"])?></td>
           <td><?php echo(number_format($data[$i]["TOTAL_BUKTI"]))?></td>
           <td id="meterai_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnCheck_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnCheck" onclick="checkDocument('NOTSTAMP', '<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Cek</button></td>
           <td id="stamp_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnStamp_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnStamp" onclick="setStamp('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">SUDAH STAMP</button></td>
           <td id="cancel_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnCancel_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnCancel" onclick="cancelMeterai('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Batalkan</button></td>
           <td id="changedoc_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnChangeDoc_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnChangeDoc" onclick="changeDoc('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Ganti Dokumen</button></td>
           <td id="remove_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnRemove_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnRemove" onclick="removeMeterai('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">HAPUS</button></td>
           </tr>
      <?php $no += 1;
        }
        echo "</tbody>"; 
      ?>
    </table>


    <h3>METERAI BATAL <font color="#80ff45" style="background-color:#1f0887;"><?php echo($branch);?></font></h3>
    <table id="TableCancelled" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        echo "<th>KeteranganBatal</th>";
        echo "<th>StampingDate</th>";
        echo "<th>CekDokumen</th>";
        echo "<th>SetStamp</th>";
        echo "<th>SetNotStamp</th>";
        echo "<th>Ganti<br>Dokumen</th>";
        echo "<th>TidakAda<br>DiSettlement</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $meteraiCancelled;
        for($i=0;$i<count($data);$i++) { 
      ?>
           <tr id="tr_<?php echo($data[$i]["SERIAL_NUMBER"])?>">
           <td><?php echo($no)?></td>
           <td><?php echo($data[$i]["SERIAL_NUMBER"])?></td>
           <td><?php echo($data[$i]["CDATE"])?></td>
           <td><?php echo($data[$i]["TYPE_TRANS"])?></td>
           <td><?php echo($data[$i]["NO_BUKTI"]."<br>".$data[$i]["KET"])?></td>
           <td><?php echo($data[$i]["NM_PLG"]."<br>".$data[$i]["KD_PLG"]."<br>".$data[$i]["NPWP"])?></td>
           <td><?php echo($data[$i]["CANCELLEDNOTE"])?></td>
           <td><?php echo($data[$i]["STAMPEDDATE"])?></td>
           <td id="meterai_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnCheck_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnCheck" onclick="checkDocument('CANCELLED', '<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Cek</button></td>
           <td id="stamp_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnStamp_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnStamp" onclick="setStamp('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">SUDAH STAMP</button></td>
           <td id="unstamp_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnUnstamp_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnUnstamp" onclick="setNotStamp('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">BELUM STAMP</button></td>
           <td id="changedoc_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnChangeDoc_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnChangeDoc" onclick="changeDoc('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Ganti Dokumen</button></td>
           <td id="remove_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnRemove_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnRemove" onclick="removeMeterai('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">HAPUS</button></td>
           </tr>
      <?php $no += 1;
        }
        echo "</tbody>"; 
      ?>
    </table>

    <h3>METERAI DISTAMP <font color="#80ff45" style="background-color:#1f0887;"><?php echo($branch);?></font></h3>
    <table id="TableStamped" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Serial Number</th>";
        echo "<th>CreatedDate</th>";
        echo "<th>Jenis</th>";
        echo "<th>NoDokumen</th>";
        echo "<th>LawanTransaksi</th>";
        // if($_SESSION["can_delete"] == 1)
        echo "<th>TotalDokumen</th>";
        echo "<th>StampingDate</th>";
        echo "<th>CekDokumen</th>";
        echo "<th>SetBatal</th>";
        echo "<th>Ganti<br>Dokumen</th>";
        echo "<th>TidakAda<br>DiSettlement</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $no = 1;
        $data = $meteraiStamped;        
        for($i=0;$i<count($data);$i++) { 
      ?>
           <tr id="tr_<?php echo($data[$i]["SERIAL_NUMBER"])?>">
           <td><?php echo($no)?></td>
           <td><?php echo($data[$i]["SERIAL_NUMBER"])?></td>
           <td><?php echo($data[$i]["CDATE"])?></td>
           <td><?php echo($data[$i]["TYPE_TRANS"])?></td>
           <td><?php echo($data[$i]["NO_BUKTI"]."<br>".$data[$i]["KET"])?></td>
           <td><?php echo($data[$i]["NM_PLG"]."<br>".$data[$i]["KD_PLG"]."<br>".$data[$i]["NPWP"])?></td>
           <td><?php echo(number_format($data[$i]["TOTAL_BUKTI"]))?></td>
           <td><?php echo($data[$i]["STAMPEDDATE"])?></td>
           <td id="meterai_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnCheck_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnCheck" onclick="checkDocument('STAMP', '<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Cek</button></td>
           <td id="cancel_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnCancel_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnCancel" onclick="cancelMeterai('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Batalkan</button></td>
           <td id="changedoc_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnChangeDoc_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnChangeDoc" onclick="changeDoc('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">Ganti Dokumen</button></td>
           <td id="remove_<?php echo($data[$i]["SERIAL_NUMBER"])?>"><button id="btnRemove_<?php echo($data[$i]["SERIAL_NUMBER"])?>" class="btnRemove" onclick="removeMeterai('<?php echo($data[$i]["SERIAL_NUMBER"])?>', '<?php echo($data[$i]["TYPE_TRANS"])?>','<?php echo($data[$i]["NO_BUKTI"])?>', <?php echo($databaseId)?>)">HAPUS</button></td>
           </tr>
      <?php $no += 1;
        }
        echo "</tbody>"; 
      ?>
    </table>

  </div>

<?php form_open(); ?>
<?php form_close(); ?>