<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<?php if (isset($content_html)) echo($content_html);?>
<style type="text/css">
  button {
    color:black;
  }
</style>
<script>
  var ListDealer = <?php echo(json_encode($ListDealer));?>;
  var dealers = <?php echo(json_encode($Dealers));?>;
  var FLAG = "";
  var strAlert = "<?php echo($alert);?>";
  var kenaikanCLRED = <?php echo($kenaikanCL_RED);?>;
  
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
 

  $(document).ready(function(){
    if (strAlert!="") {
      alert(strAlert);
    }
    
    $("#StatusCBD").val("");
    $("#StatusCBD").hide();
    $("#StatusLOCK").val("");
    $("#StatusLOCK").hide();
    $("#maks-limit-row").hide();
    $(".form-request-cl").hide();

    $("#txtPlg").autocomplete({
      source: dealers
    });

    $('#txtPlg').on('change', function() {
      FLAG = "";

      var Dealer = $("#txtPlg").val();
      var sArray = Dealer.split(" - ");
      var KdPlg = sArray[1];
      $("#txtKodePlg").val(sArray[1]);
      $("#txtNamaPlg").val(sArray[0]);
      $("#txtWilayahPlg").val(sArray[2]);

      var Div = $("#selDiv").val();

      if ((sArray[2].substring(0,2)=="MO" || sArray[2].substring(0,2)=="DM") && (Div!="MO")) {
        alert("Dealer MO hanya bisa menggunakan Divisi MO. Divisi otomatis diganti ke MO!");
        $("#selDiv").val("MO");
      } else if (sArray[2]=="PROYEK") {
        alert("Dealer PROYEK menggunakan CL MISHIRIN untuk pengetikan Produk+Sparepart. Divisi otomatis diganti ke MISHIRIN!");
        $("#selDiv").val("MISHIRIN");  
      } else if (Div=="MO" && sArray[2].substring(0,2)!="MO" && sArray[2].substring(0,2)!="DM") {
        alert("Dealer Non MO tidak bisa menggunakan Divisi MO. Divisi otomatis diganti ke MISHIRIN. Ganti manual ke SPAREPART untuk pengajuan limit sparepart!");
        $("#selDiv").val("MISHIRIN");
      }

      for(var d in ListDealer)
      {
        if (ListDealer[d].KD_PLG==KdPlg) {
          $("#txtAlamatPlg").val(ListDealer[d].ALM_PLG);
          
          if (ListDealer[d].STATUSCBD==1) {
            $("#StatusCBD").val("DEALER CBD");
            $("#StatusCBD").show();
          } else {
            $("#StatusCBD").val("");
            $("#StatusCBD").hide();
          }

          break;
        }
      }
      $("#txtJTTerlama").css("background-color","white");
      
      LoadCL();
      LoadLastTenRequests();
      $(".form-request-cl").hide();
    });

    $("#btnRequestCL").click(function(){
      var Wilayah=$("#txtWilayahPlg").val();
      var CLPermanent = $("#txtLiPerma").val();
      $(".form-request-cl").show();

      LoadCLGroup();
      
      if (Wilayah.substring(0,2)=="MO" || Wilayah=="DM" || Wilayah=="PROYEK") {
        // Intervensi untuk MODERN OUTLET Tidak Mempedulikan Warna Seperti Di Tradisional
        $("#txtLiMaks").val("UNLIMITED");
        $("#maks-limit-row").hide();
      }

      var CLPermanent = $("#txtLiPerma").val();
      $("#txtIncrement").val(0);
      $("#txtRequest").val(CLPermanent);
    });

    $("#btnRefreshCL").click(function(){
      LoadCL();
      LoadLastTenRequests();
    });

    $('#selDiv').on('change', function() {
      var Wilayah = $("#txtWilayahPlg").val();
      var Divisi = $("#selDiv").val();

      if (Wilayah.substring(0,2)=="MO" || Wilayah.substring(0,2)=="DM") {
        if (Divisi!="MO") {
          alert("Khusus Dealer MO/DM Hanya Mengenal 1 CL.\r\nDivisi Otomatis Dipindah ke MO"); //MO/DM
          $("#selDiv").val("MO");
        }
      } else if (Wilayah=="PROYEK") {
        if (Divisi!="MISHIRIN") {
          alert("Khusus Dealer PROYEK Hanya Mengenal 1 CL.\r\nDivisi Otomatis Dipindah ke MISHIRIN");
          $("#selDiv").val("MISHIRIN");
        }
      } else if (Divisi=="MO" && Wilayah!="") {
        alert("Dealer NON MO Tidak Boleh Memilih Divisi MO.\r\nDivisi otomatis dipindah ke MISHIRIN");
        $("#selDiv").val("MISHIRIN");
      }
      LoadCL();
      LoadLastTenRequests();
    });

    $('#txtRequest').on('change', function() {
      var numberVal = $(this).val();
      while (numberVal.search(",")>-1) {
        numberVal = numberVal.replace(",","");
      }
      $(this).val(number_format(numberVal));
    });

    $('#txtIncrement').on('change', function() {
      var Divisi= $("#selDiv").val();

      var increment = $(this).val();
      while (increment.search(",")>-1) {
        increment = increment.replace(",","");
      }
      var CLPermanent = ($("#txtLiPerma").val()=="")?"0":$("#txtLiPerma").val();
      while (CLPermanent.search(",")>-1) {
        CLPermanent = CLPermanent.replace(",","");
      }
      var CLTemporary = ($("#txtLiTemp").val()=="")?"0":$("#txtLiTemp").val();
      while (CLTemporary.search(",")>-1) {
        CLTemporary = CLTemporary.replace(",","");
      }      
      var CL = ((CLTemporary>CLPermanent)?CLTemporary:CLPermanent);

      //alert($("#txtLiMaks").val());

      if ($("#txtLiMaks").val()=="UNLIMITED") {

        var CLNew = Number(CL) + Number(increment);
        $(this).val(number_format(increment));
        $("#txtRequest").val(number_format(CLNew));

      } else {

        var CLMaks = ($("#txtLiMaks").val()=="")?"0":$("#txtLiMaks").val();
        while (CLMaks.search(",")>-1) {
          CLMaks = CLMaks.replace(",","");
        }

        var CLNew = Number(CL) + Number(increment);
        if (CLNew<=CLMaks) {
          $(this).val(number_format(increment));
          $("#txtRequest").val(number_format(CLNew));
        } else if (Divisi!="SPAREPART") {
          alert("Kenaikan Credit Limit Tidak Boleh Melebihi Credit Limit Maksimum");
          increment = CLMaks - CL;
          CLNew = CLMaks;
          $(this).val(number_format(increment));
          $("#txtRequest").val(number_format(CLNew));
        } else {
          $(this).val(number_format(increment));
          $("#txtRequest").val(number_format(CLNew));        
        }

      }
    });

    $(".btnRemove").click(function(){
      alert("remove");
    });
  });

  var LoadCL = function(){
    var KdPlg = $("#txtKodePlg").val();
    var NmPlg = $("#txtNamaPlg").val();
    var WilPlg= $("#txtWilayahPlg").val();
    var Divisi= $("#selDiv").val();
    $("#maks-limit-row").hide();
    $("#StatusLOCK").val("");
    $("#StatusLOCK").hide();

    $("#txtLiPerma").val(number_format(0));
    $("#txtLiTemp").val(number_format(0));
    $("#txtPiutang").val(number_format(0));
    $("#txtLiMaks").val(number_format(0));
    $("#txtJTTerlama").val("");
    
    if (KdPlg!="" && NmPlg!="" && WilPlg!="") {     
      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo site_url('MsDealer/GetCreditLimit'); ?>", {
        KdPlg     : KdPlg,
        NmPlg     : NmPlg,
        WilPlg    : WilPlg,
        Divisi    : Divisi,
        csrf_bit  : csrf_bit
      }, function(data){
        if (data.result == "sukses") {
          var cl = data.data;
          //alert(cl.CL_PERMANENT);
          $("#txtLiPerma").val(number_format(cl.CL_PERMANENT));
          $("#txtLiTemp").val(number_format(cl.CL_TEMPORARY));
          $("#txtPiutang").val(number_format(cl.PIUTANG));
          //$("#txtLiMaks").val(number_format(cl.CL_MAKS));
          var CLMaks = 0;

          $("#txtJTTerlama").val(cl.JT_FAKTUR_TERLAMA);
          if (cl.FKGANTUNG==true) {
            $("#txtJTTerlama").css("background-color","red");
            $("#txtJTTerlama").css("color","white");
          } else {
            $("#txtJTTerlama").css("background-color","#026305");
            $("#txtJTTerlama").css("color","white");
          }

          /*7 MARET 2022: MARKING MERAH BOLEH NAIK LIMIT MAKS 100JT */
          // if (Number(cl.CL_PERMANENT)<100000000) {
          //   CLMaks = Number(cl.CL_PERMANENT) + 20000000;
          // } else {
          //   CLMaks = 1.2 * Number(cl.CL_PERMANENT);
          // }
          
          $("#txtLiMaks").val(number_format(CLMaks));
          $("#btnRequestCL").hide();

          if (cl.AKTIF=="T") {
            $("#StatusLOCK").val("TERKUNCI");
            $("#StatusLOCK").show();
            //alert("TOKO TERKUNCI");
            if (Divisi!="SPAREPART") {
              if (cl.FKGANTUNG==false || "SKIP"=="SKIP") {
                $("#btnRequestCL").show();
              }
            }
          } else if (cl.AKTIF=="N") {
            $("#StatusLOCK").val("TIDAK AKTIF");
            $("#StatusLOCK").show();
          } else if ($("#StatusCBD").val()=="DEALER CBD") {
            $("#btnRequestCL").hide();
          } else if (cl.FKGANTUNG==false || "SKIP"=="SKIP") {
            $("#btnRequestCL").show();
          }
          $(".loading").hide();
        } else {
          $(".loading").hide();
          alert(data.ket);
        }
      },'json',errorAjax);    
    }

    $("#tableGroupDealerBody").html("");
    $("#txtRequest").val(0);
  }

  var LoadCLGroup = function(){
    var KdPlg = $("#txtKodePlg").val();
    var NmPlg = $("#txtNamaPlg").val();
    var Divisi= $("#selDiv").val();
    var WilPlg= $("#txtWilayahPlg").val();

    $("#tableGroupDealerBody").html(""); 
    FLAG="";

    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('MsDealer/GetCreditLimitGroup'); ?>", {
      KdPlg     : KdPlg,
      NmPlg     : NmPlg,
      WilPlg    : WilPlg,
      Divisi    : Divisi,
      csrf_bit  : csrf_bit
    }, function(data){
      if (data.result == "sukses") {
        var List = data.data;
        var BRS = "";

        for(var i=0;i<List.length;i++) {

          var KDLOKASI = ""+List[i].KD_LOKASI+"";
          var KDPLG = ""+List[i].NM_PLG+"<br>"+List[i].KD_PLG;
          var LIMITPERMANEN = number_format(List[i].CL_PERMANENT);
          var LIMITTEMPORARY = number_format(List[i].CL_TEMPORARY);
          var PIUTANG = number_format(List[i].PIUTANG);
          var TGLFAKTURTERLAMA = List[i].TGL_FAKTUR_TERLAMA;
          var JTFAKTURTERLAMA = List[i].JT_FAKTUR_TERLAMA;
          var MOBILE = KDPLG+"<br>"+KDLOKASI+"<br>";        

          MOBILE+= "CL PERMANENT: "+LIMITPERMANEN+"<br>";
          MOBILE+= "CL TEMPORARY: "+LIMITTEMPORARY+"<br>";
          MOBILE+= "PIUTANG: "+PIUTANG+"<br>";
          MOBILE+= "TGL FK TERLAMA: "+TGLFAKTURTERLAMA+"<br>";
          MOBILE+= "JT FK TERLAMA: "+JTFAKTURTERLAMA+"<br>";
          // alert(MOBILE);
          var TELAT = List[i].MAKS_TELAT;
          // alert(TELAT);
          // var STYLE = "background-color:"+((TELAT>14)?"red":((TELAT>7)?"yellow":"green"))+";";
          var STYLE = "background-color:"+((TELAT>7)? "red":"green")+";";

          if (FLAG!="RED") {
            if (WilPlg=="PROYEK") {
              FLAG = ((TELAT>7)? "RED" : "GREEN");
            } else {
              FLAG = ((TELAT>7)? "RED" : "GREEN");
            }
          }

          BRS ="";
          BRS+="<tr style='padding:5px;' id='tr_"+KDLOKASI+"_"+KDPLG+"'>";
          BRS+="  <td style='"+STYLE+"'>"+KDLOKASI+"</td>";
          BRS+="  <td class='hideOnMobile' style=''>"+KDPLG+"</td>";
          BRS+="  <td class='hideOnMobile' style='text-align:right;'>"+LIMITPERMANEN+"</td>";
          BRS+="  <td class='hideOnMobile' style='text-align:right;'>"+LIMITTEMPORARY+"</td>";
          BRS+="  <td class='hideOnMobile' style='text-align:right;'>"+PIUTANG+"</td>";
          BRS+="  <td class='hideOnMobile' style=''>"+TGLFAKTURTERLAMA+"</td>";
          BRS+="  <td class='hideOnMobile' style=''>"+JTFAKTURTERLAMA+"</td>";
          BRS+="  <td class='colMobile' style=''>"+MOBILE+"</td>";
          BRS+="</tr>";

          //alert(BRS);
          $("#tableGroupDealerBody").append(BRS);
        }

        // if (WilPlg.substring(0,2)=="MO" || WilPlg=="PROYEK") {
        //   $("#txtLiMaks").val("UNLIMITED");
        // } else 
        $("#txtMarking").val(FLAG);

        if ((FLAG=="RED") && (Divisi=="SPAREPART")) {
          $("#maks-limit-row").show();

          var CLPermanent = ($("#txtLiPerma").val()=="")?"0" : $("#txtLiPerma").val();
          while (CLPermanent.search(",")>-1) {
            CLPermanent = CLPermanent.replace(",","");
          }
          $("#txtLiMaks").val(number_format(CLPermanent));
        
        } else if ((FLAG=="RED") && (Divisi!="SPAREPART")) {
          $("#maks-limit-row").show();

          var CLPermanent = ($("#txtLiPerma").val()=="")?"0":$("#txtLiPerma").val();
          while (CLPermanent.search(",")>-1) {
            CLPermanent = CLPermanent.replace(",","");
          }

          var CLMaks = parseInt(CLPermanent) + kenaikanCLRED;
          $("#txtLiMaks").val(number_format(CLMaks));

        } else {
          $("#txtLiMaks").val("UNLIMITED");
        }

        $(".loading").hide();
      } else {
        alert(data.ket);
        $(".loading").hide();
      }
    },'json',errorAjax);
  }

  var LoadLastTenRequests = function(){
    var KdPlg = $("#txtKodePlg").val();
    var Divisi= $("#selDiv").val();
    $("#tableHistoryBody").html("");
    
    $(".loading").show();
    var csrf_bit = $("input[name=csrf_bit]").val();
    $.post("<?php echo site_url('MsDealer/GetLastTenRequests'); ?>", {
      KdPlg     : KdPlg,
      Divisi    : Divisi,
      csrf_bit  : csrf_bit
    }, function(data){
      if (data.result == "sukses") {
        
        var List = data.data;
        var BRS = "";

        for(var i=0;i<List.length;i++) {

          var TGLREQ = ""+List[i].RequestDateStr+"";
          var CLAWAL = number_format(List[i].CLAwal);
          var CLBARU = number_format(List[i].CLBaru);
          var PEMOHON = List[i].RequestByName;
          var STATUS = List[i].RequestStatus;
          if (STATUS=="WAITING FOR APPROVAL") {
            STATUS += " [<strong>"+List[i].ApprovedCount+"/"+List[i].ApprovalNeeded+"</strong>]";
            STATUS += "<br>EXPIRED DATE:<br><strong>"+List[i].StrExpiryDate+"</strong>";
          } else if (STATUS=="APPROVED") {
            STATUS += " ["+List[i].ApprovedDateStr+"]<br>";
            if (List[i].BhaktiFlag=="FINISHED") {
              STATUS += "UPDATE KE BHAKTI SELESAI ["+List[i].BhaktiProcessDate+"]";
            } else {
              STATUS += "UPDATE KE BHAKTI MENUNGGU";
            }
          }

          var ACTION = "";
          ACTION = "<a href='<?php echo(site_url());?>MsDealerApproval/ProcessRequest?type=cl&id="+List[i].RequestNo+"&viewonly=yes' target='_blank'>View Request</a><br>";

          if (List[i].RequestStatus=="WAITING FOR APPROVAL") {
            ACTION+= "<a href='ResendRequest?type=CL&id="+List[i].RequestNo+"' target='_blank'><button>Resend Request</button></a>"
            ACTION+= "<a href='CancelRequest?type=CL&id="+List[i].RequestNo+"&viewonly=yes' target='_blank'>Cancel Request</a>"
          }

          var MOBILE = "TglRequest: "+TGLREQ+"<br>";
          MOBILE+= "TGL REQUEST: "+TGLREQ+"<br>";
          MOBILE+= "CL AWAL: "+CLAWAL+"<br>";
          MOBILE+= "CL BARU: "+CLBARU+"<br>";
          MOBILE+= "PEMOHON: "+PEMOHON+"<br>";
          MOBILE+= "STATUS: "+STATUS+"<br>";
          MOBILE+= ACTION+"<br>";


          BRS ="";
          BRS+="<tr style='padding:5px;' id='tr_"+List[i].RequestID+"'>";
          BRS+="  <td class='hideOnMobile' style=''>"+TGLREQ+"</td>";
          BRS+="  <td class='hideOnMobile' style='text-align:right;'>"+CLAWAL+"</td>";
          BRS+="  <td class='hideOnMobile' style='text-align:right;'>"+CLBARU+"</td>";
          BRS+="  <td class='hideOnMobile' style=''>"+PEMOHON+"</td>";
          BRS+="  <td class='hideOnMobile' style=''>"+STATUS+"</td>";
          BRS+="  <td class='hideOnMobile' style=''>"+ACTION+"</td>";
          BRS+="  <td class='colMobile' style=''>"+MOBILE+"</td>";
          BRS+="</tr>";
          //alert(BRS);

          $("#tableHistoryBody").append(BRS);
        }
        $(".loading").hide();
      } else {
        $(".loading").hide();
        //alert(data.ket);
      }
    },'json',errorAjax);    

    $("#tableGroupDealerBody").html("");
    $("#txtRequest").val(0);
  }

</script>
<div class="container">
  <h3 style="text-align:center;">REQUEST CL</h3>
  <?php echo form_open(site_url('MsDealer/RequestCL')); ?>
    <div class="form-group">
      <label>Divisi</label>
      <select class="form-control" id="selDiv" name="selDiv">
        <option value="MISHIRIN">MISHIRIN</option>
        <!--option value="CO&SANITARY">CO&SANITARY</option-->
        <option value="MO">MO</option>
        <!-- <option value="MO">MO/DM</option> -->
        <option value="SPAREPART">SPAREPART</option>
      </select>
      <small id="info-div-1" class="form-text text-muted">Please Select Divisi</small><br>
      <small id="info-div-2" class="form-text text-muted">30-Jun-2021: untuk Dealer Modern Outlet/MO Cabang hanya menggunakan 1 Credit Limit <u><strong>Harap memilih MO untuk dealer MO</strong></u></small><br>
      <!-- <small id="info-div-2" class="form-text text-muted" style="color:navy;">30-Jun-2021: untuk Dealer DM hanya menggunakan 1 Credit Limit <u><strong>Harap memilih MO/DM untuk dealer DM</strong></u></small><br> -->
      <small id="info-div-2" class="form-text text-muted" style="color:navy;">01-Mei-2022: untuk Dealer TRADISIONAL, CL Produk digabung 1 <u><strong>Harap memilih MISHIRIN untuk CL PRODUK</strong></u></small><br>
      <small id="info-div-2" class="form-text text-muted" style="color:navy;">01-Mei-2022: untuk Dealer PROYEK hanya menggunakan 1 Credit Limit <u><strong>Harap memilih MISHIRIN untuk dealer PROYEK</strong></u></small>
    </div>
    <div class="form-group">
      <label>Nama Dealer</label>
      <input type="text" class="form-control" name="txtPlg" id="txtPlg" placeholder="Nama Dealer" required>
      <input type="text" class="form-control" name="txtNamaPlg" id="txtNamaPlg" placeholder="Nama Dealer" required readonly style="display:none;">
      <input type="text" class="form-control" name="txtKodePlg" id="txtKodePlg" placeholder="Kode Dealer" required readonly>
      <input type="text" class="form-control" name="txtMarking" id="txtMarking" placeholder="GREEN/RED" required readonly style="display:none;">
    </div>
    <div class="form-group">
      <label>Alamat Dealer</label>
      <textarea class="form-control" name="txtAlamatPlg" id="txtAlamatPlg" placeholder="Alamat Dealer" rows="4" required readonly></textarea>
    </div>
    <div class="form-group">
      <label>Wilayah Dealer</label>
      <input type="text" class="form-control" name="txtWilayahPlg" id="txtWilayahPlg" placeholder="Wilayah Dealer" required readonly>
    </div>
    <div class="form-group">
      <label>Limit Permanent</label>
      <input type="text" class="form-control" name="txtLiPerma" id="txtLiPerma" placeholder="0" readonly>
    </div>
    <div class="form-group">
      <label>Limit Temporary</label>
      <input type="text" class="form-control" name="txtLiTemp" id="txtLiTemp" placeholder="0" readonly>
    </div>
    <div class="form-group">
      <label>Piutang</label>
      <input type="text" class="form-control" name="txtPiutang" id="txtPiutang" placeholder="0" readonly>
    </div>
    <div class="form-group">
      <label>JT FAKTUR TERLAMA</label>
      <input type="text" class="form-control" name="txtJTTerlama" id="txtJTTerlama" placeholder="dd-MMM-yyyy" readonly>
    </div>
    <div class="form-group" id="maks-limit-row">
      <label>Maks Limit</label>
      <input type="text" class="form-control" name="txtLiMaks" id="txtLiMaks" placeholder="0" readonly>
    </div>    
    <div class="form-group form-request-cl">
      <label>Kenaikan CL Yang Diinginkan</label>
      <input type="text" class="form-control" name="txtIncrement" id="txtIncrement" required>
      <i aria-hidden="true" >Kenaikan CL <strong>Sparepart</strong> Yang Disetujui Berlaku s/d Akhir Bulan</i>
      <br><i aria-hidden="true" >Kenaikan CL Product Temporary <u>Berlaku 7 Hari</u> sejak Disetujui atau s/d Akhir Bulan (<strong>Mana Yang Lebih Dulu</strong>)</i>
    </div>
    <div class="form-group form-request-cl">
      <label>CL Baru Final</label>
      <input type="text" class="form-control" name="txtRequest" id="txtRequest" required readonly>
    </div>
    <div class="form-group form-request-cl">
      <label>Catatan</label>
      <textarea class="form-control" name="txtCatatan" id="txtCatatan" placeholder="Catatan untuk Permintaan Approval (Opsional/Tidak Wajib)" rows="4"></textarea>
    </div>
    <div class="form-group form-request-cl">
      <input type="submit" class="btn btn-primary" value="Submit" id="btnSubmit">
      <input type="reset" class="btn btn-danger" value="Reset" id="btnReset" onclick="$('.form-request-cl').hide();">  
    </div>
    <div class="form-group form-request-cl">
      <label>Informasi Limit Group Pelanggan</label>
      <table id="tableGroupDealer" class="table table-stripped table-bordered">
        <thead>
          <tr>
            <td width="5%"></td>
            <td width="25%" class="hideOnMobile">Pelanggan</td>
            <td width="15%" class="hideOnMobile">Limit Permanen</td>
            <td width="15%" class="hideOnMobile">Limit Temporary</td>
            <td width="15%" class="hideOnMobile">Piutang</td>
            <td width="10%" class="hideOnMobile">Tgl Faktur Terlama</td>
            <td width="10%" class="hideOnMobile">Tgl Jatuh Tempo Faktur Terlama</td>
            <th scope="col" width="90%" class="colMobile">Data Pelanggan</th>
        </thead>
        <tbody id="tableGroupDealerBody">
        </tbody>
      </table>
    </div>
  <?php echo form_close(); ?>

  <div class="form-group">
    <label>10 Request Credit Limit Terakhir</label>
    <table id="tableHistory" class="table table-stripped table-bordered">
      <thead>
        <tr>
          <td class="hideOnMobile" width="10%">Tanggal Request</td>
          <td class="hideOnMobile" width="15%">Limit Awal</td>
          <td class="hideOnMobile" width="15%">Limit Baru</td>
          <td class="hideOnMobile" width="15%">Pemohon</td>
          <td class="hideOnMobile" width="30%">Status</td>
          <td class="hideOnMobile" width="15%">-</td>
          <th scope="col" class="colMobile">Data Request</th>
        </tr>
      </thead>
      <tbody id="tableHistoryBody">
      </tbody>
    </table>
  </div>
</div> <!-- /container -->

<div id="PageFooter" style="position:fixed;bottom:0px;left:0px;width:100%;height:60px;background-color:navy;color:white;font-size:10pt;padding-top:10px;padding-left:10px"> 
    <div class="form-group">
      <button id="btnRefreshCL" name="btnRefreshCL">REFRESH</button>
      <button id="btnRequestCL" name="btnRequestCL">REQUEST CREDIT LIMIT</button>
      <input type="text" id="StatusLOCK" name="StatusLOCK" style="background-color:red;color:white;text-align:center;" readonly value="">
      <input type="text" id="StatusCBD" name="StatusCBD" style="background-color:#e38400;color:white;text-align:center;" readonly value="">
    </div>
</div>


