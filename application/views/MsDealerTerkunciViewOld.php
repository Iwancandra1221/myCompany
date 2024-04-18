<style type="text/css">
</style>
<script>

  $( document ).ready(function(){
    var err = "<?php echo($alert); ?>";
    if (err!="") {
      alert(err);
    }

    $(".btnRequest").click(function(){
        var kdPlg = $(this).attr("kode");
        var nmPlg = $(this).attr("nama");
        var wil = $(this).attr("wil");

        $(".loading").show();
        var csrf_bit = $("input[name=csrf_bit]").val();
        $.post("<?php echo site_url('MsDealer/GetRequestUnlockByToko'); ?>", {
          KdPlg  : kdPlg,
          csrf_bit: csrf_bit
        }, function(data){
          if (data.result == "sukses") {
            $("#PopUpForm #formTitle").html("REQUEST BUKA LOCK TOKO");
            $("#PopUpForm #KDPLG").val(kdPlg);
            $("#PopUpForm #NMPLG").val(nmPlg);
            $("#PopUpForm #WILAYAH").val(wil);
            $("#PopUpForm #KET").text("");
            $("#PopUpForm").show();
          } else {
            alert(data.ket);
          }
        },'json',errorAjax);    
        $(".loading").hide();      
    });

    $("#btnCancel").click(function(){
      $(".ClosePopUp").click();
    });

    $("#btnSend").click(function(){
      $("#FormRequest").submit();
    });

    $(".btnFakturGantung").click(function(){
        var kdPlg = $(this).attr("kode");
        var nmPlg = $(this).attr("nama");
        var wil = $(this).attr("wil");

        $(".loading").show();
        var csrf_bit = $("input[name=csrf_bit]").val();
        $.post("<?php echo site_url('MsDealer/GetFakturGantung'); ?>", {
          KdPlg  : kdPlg,
          csrf_bit: csrf_bit
        }, function(data){
          if (data.result == "sukses") {
            var faktur=data.faktur;
            var x = "";
            $("#TabelFakturGantung #FG_NMPLG").html("<h4>FAKTUR GANTUNG "+nmPlg+"</h4>");

            for(var i=0; i<faktur.length; i++) { 
              n = i+1;
              x = x + "<tr>";
              x = x + " <td class='hideOnMobile'>"+n+"</td>";
              x = x + " <td>"+faktur[i].NO_FAKTUR+"</td>";
              x = x + " <td>"+faktur[i].TGL_FAKTUR+"</td>";
              x = x + " <td class='hideOnMobile'>"+faktur[i].TGL_JATUHTEMPO+"</td>";
              x = x + " <td class='hideOnMobile' align='center'>"+faktur[i].TELAT+"</td>";
              x = x + " <td class='hideOnMobile' align='right'>"+faktur[i].TOTAL_FAKTUR+"</td>";
              x = x + " <td class='hideOnMobile' align='right'>"+faktur[i].SISA_FAKTUR+"</td>";
              x = x + " <td class='hideOnMobile'>"+faktur[i].DIVISI+"</td>";
              x = x + " <td class='hideOnMobile'>"+faktur[i].SALESMAN+"</td>";
              x = x + "</tr>";
            }
            $("#TblFakturGantungBody").html(x);
            $("#TblFakturGantung").DataTable();
            $("#TabelFakturGantung").show();
            $(".loading").hide();      
          } else {
              alert(data.ket);
              $(".loading").hide();      
          }
        },'json',errorAjax);    
    });

    $("#btnClose").click(function(){
      $(".ClosePopUp").click();
    });

    $(".btnResend").click(function(){
      var ReqID = $(this).attr("reqid");
      $(".loading").show();
        var csrf_bit = $("input[name=csrf_bit]").val();
        $.post("<?php echo site_url('MsDealer/ResendRequestUnlock'); ?>", {
          KodeRequest : ReqID,
          csrf_bit    : csrf_bit
        }, function(data){
          if (data.result == "sukses") {
            alert("Request Telah Dikirim Ulang");
            $(".loading").hide();      
          } else {
            alert("Request Gagal Dikirim Ulang: "+data.err);
            $(".loading").hide();      
        }
        },'json',errorAjax);  
    });

    $(".btnCancelReq").click(function(){
      var ReqID = $(this).attr("reqid");
      var BtnID = $(this).attr("btnid");
      var Ket = prompt("Alasan Pembatalan:")

      if (Ket=="") {
        alert ("Alasan Tidak Boleh Kosong");
      } else {
        $(".loading").show();
        var csrf_bit = $("input[name=csrf_bit]").val();
        $.post("<?php echo site_url('MsDealer/CancelRequestUnlock'); ?>", {
          KodeRequest : ReqID,
          KetCancel   : Ket,
          csrf_bit    : csrf_bit
        }, function(data){
          if (data.result == "sukses") {
            var REQ = data.request;
            alert("Request Telah Dicancel!");
            location.reload();
            // $("#colReq"+BtnID).html("<b>REQUEST DICANCEL.</b><br><div class='btn btnRequest' kode='"+REQ.KdPlg+"' nama='"+REQ.NmPlg+"' wil='"+REQ.Wilayah+"'>Buat<br>Request</div>");
            $(".loading").hide();      
          } else {
            alert("Cancel Request Gagal: "+data.err);
            $(".loading").hide();      
          }
        },'json',errorAjax);
      }
    });
  });
</script>
<?php //echo(json_encode($_SESSION)."<br><br>");?>
<div class="container">
  <h5>BERIKUT ADALAH LIST TOKO2 YANG TERKUNCI SECARA OTOMATIS PADA HARI INI (KARENA ADA FAKTUR-FAKTUR TERTENTU YANG MASIH BELUM DILUNASI).</h5>
  <!-- <font color="blue"><h5>UNTUK MELIHAT DETAIL LIST NOMOR-NOMOR FAKTURNYA SILAHKAN KE MENU <a href="viewRekapFakturJT">REKAP FAKTUR JATUH TEMPO</a></h5></font> -->
  <table id="maintable" class="table table-striped table-bordered" cellspacing="0">
    <thead>
      <tr>
        <td class="hideOnMobile">No</td>
        <td class="hideOnMobile">Kode</td>
        <td>Nama Pelanggan</td>
        <td class="hideOnMobile">Wilayah</td>
        <td class="hideOnMobile">CreditLimit</td>
        <td></td>        
        <td></td>
      </tr>
    </thead>
    <tbody>
      <?php for($i=0;$i<count($dealers);$i++) {
          $no = $i + 1;
          echo("<tr>");
          echo("  <td class='hideOnMobile'>".$no."</td>");
          echo("  <td class='hideOnMobile'>".$dealers[$i]["KD_PLG"]."</td>");
          echo("  <td>".$dealers[$i]["NM_PLG"]."</td>");
          echo("  <td class='hideOnMobile'>".$dealers[$i]["WILAYAH"]."</td>");
          echo("  <td class='hideOnMobile'>CL MISHIRIN : ".number_format($dealers[$i]["CL_MISHIRIN"])."<br>CL CO&SANITARY : ".number_format($dealers[$i]["CL_COSANITARY"])."</td>");
          
          if (($_SESSION["branchID"]=="JKT" && $MO==true) ||
              ($_SESSION["branchID"]=="JKT" && substr($dealers[$i]["WILAYAH"],0,2)=="MO" && trim($dealers[$i]["WILAYAH"])!="MODERN OUTLET") ||
              ($_SESSION["branchID"]!="JKT" && substr($dealers[$i]["WILAYAH"],0,2)=="MO" && trim($dealers[$i]["WILAYAH"])!="MODERN OUTLET") ||
              ($_SESSION["branchID"]!="JKT" && $dealers[$i]["WILAYAH"]=="PROYEK")) {
            if ($dealers[$i]["REQUEST"]==null) {
              echo("  <td id='colReq".$no."'><div class='btn btnRequest' kode='".$dealers[$i]["KD_PLG"]."' nama='".$dealers[$i]["NM_PLG"]."' wil='".$dealers[$i]["WILAYAH"]."'>Buat<br>Request</div></td>");
            } else {
              echo("  <td id='colReq".$no."'>");
              if ($dealers[$i]["REQUEST"]->IsApproved==0) {
              echo("  <b>Request Unlock Ditemukan<br>[MENUNGGU APPROVAL]</b>");
              echo("  <br><button class='btnResend' reqid='".$dealers[$i]["REQUEST"]->RequestID."'>RESEND EMAIL</button>");
              echo("  <button class='btnCancelReq' reqid='".$dealers[$i]["REQUEST"]->RequestID."' btnid=".$no.">CANCEL REQUEST</button>");
              } else {
              echo("  <b><font color='#015208'>Request Unlock Ditemukan [APPROVED]<br>Unlock S/D Tgl.".date("d-M-Y",strtotime($dealers[$i]["REQUEST"]->UnlockEnd))."</font></b>");
              // echo("  <br><button class='btnResync' reqid='".$dealers[$i]["REQUEST"]->RequestID."'>RESYNC KE BHAKTI</button>");
    
              }
              echo("  </td>");
            }
          } else {
              echo("  <td></td>");
          }
          echo("  <td><div class='btn btnFakturGantung' kode='".$dealers[$i]["KD_PLG"]."' nama='".$dealers[$i]["NM_PLG"]."' wil='".$dealers[$i]["WILAYAH"]."'>Faktur<br>Gantung</div></td>");
          echo("</tr>");
      }?>
    </tbody>
  </table>
</div>

<div class="PopUpForm" id="PopUpForm">
  <div class="overlay"></div>
  <div class="loadingItem">
    <?php echo form_open('MsDealer/RequestBukaLockToko', array("id"=>"FormRequest")); ?>  
    <form style="width:450px; height:420px;">
      <i class="fa fa-times ClosePopUp"></i>
      <div class="popupform_title" id="formTitle"></div>
      <div class="row">
        <div class="col-3 col-m-5">NAMA DEALER</div>
        <div class="col-9 col-m-7"><input type="text" id="NMPLG" name="NMPLG" readonly></div>
      </div>
      <div class="row">
        <div class="col-3 col-m-5">KODE DEALER</div>
        <div class="col-9 col-m-7"><input type="text" id="KDPLG" name="KDPLG" readonly></div>
      </div>
      <div class="row">
        <div class="col-3 col-m-5">WILAYAH</div>
        <div class="col-9 col-m-7"><input type="text" id="WILAYAH" name="WILAYAH" readonly></div>
      </div>
      <div class="row">
        <div class="col-3 col-m-5">CATATAN</div>
        <div class="col-9 col-m-7"><textarea id="KET" name="KET" rows="4" cols="50"></textarea></div>
      </div>      
      <div class="row">
        <div class="col-3 col-m-3"></div>
        <div class="col-6 col-m-6">
          <div class="btn" id="btnSend" name="btnSend">Kirim Request</div>
          <div class="btn" id="btnCancel" name="btnCancel">Batal</div>
        </div>
      </div>
    </form>
    <?php echo form_close();?>
  </div>
</div>

<div class="PopUpForm" id="TabelFakturGantung">
  <div class="overlay"></div>
  <div class="loadingItem">
    <form style="width:95%!important; height:550px!important;padding:10px;overflow-y:scroll;">
      <i class="fa fa-times ClosePopUp"></i>
      <div class="row">
        <div class="col-10 col-m-10" id="FG_NMPLG"></div>
        <div class="col-2 col-m-2">
          <div class="btn" id="btnClose" name="btnClose">CLOSE</div>
        </div>
      </div>
      <table id="TblFakturGantung" class="table table-striped table-bordered" cellspacing="0">
        <thead>
          <tr>
            <td class='hideOnMobile'>No</td>
            <td>No Faktur</td>
            <td>Tgl Faktur</td>
            <td class='hideOnMobile'>Tgl JatuhTempo</td>
            <td class='hideOnMobile'>Telat</td>
            <td class='hideOnMobile'>Total Faktur</td>
            <td class='hideOnMobile'>Sisa Faktur</td>
            <td class='hideOnMobile'>Divisi</td>
            <td class='hideOnMobile'>Salesman</td>
          </tr>
        </thead>
        <tbody id="TblFakturGantungBody">
        </tbody>
      </table>
      <div style="height:50px;"></div>
    </form>

  </div>
</div>
