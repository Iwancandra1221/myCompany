<link href='<?php echo base_url(); ?>css/datetimepicker.css' rel='stylesheet' type='text/css'>
<link rel="icon" href="<?php echo base_url();?>images/icon.png" type="image/x-icon">

<!-- Bootstrap -->
<link href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
<script src="<?php echo base_url('assets/bootstrap/js/bootstrap.min.js');?>"></script>
<link href="<?php echo base_url('assets/bootstrap/css/datatables.bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
<script src="<?php echo base_url('assets/bootstrap/js/datatables.bootstrap.min.js');?>"></script>
<script src="<?php echo base_url('assets/bootstrap/js/datatables.min.js');?>"></script>

<script>
  var ListDealer = <?php echo(json_encode($ListDealer)); ?>;

  activateDatepicker = function(){
    $('.datepicker').datetimepicker({
      scrollMonth:false,
      lang:'en',
      timepicker:false,
      format:'m/d/Y',
      formatDate:'m/d/Y',
      todayButton:true
    });
  }

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

  var RefreshListDealer = function() {
      var f_wilayah = $("#f_wilayah").val();
      var f_aktif = $("#f_aktif").val();
      //var f_cbd = $("#f_cbd").val();
      var f_cbd = "ALL";
      var f_nama = $("#f_nama").val();
      //alert(f_nama);
      var NO = 0;
      $("#tblDealerBody").html("");
      $(".loading").show();

      var TotalDealer = ListDealer.length;
      for(var i=0;i<TotalDealer;i++) {
        var append = false;
        var wilOK = false;
        var aktifOK = false;
        var cbdOK= false;
        var namaOK = false;
      
        if (f_wilayah=="ALL") {
          wilOK = true;
        } else if (ListDealer[i].WILAYAH==f_wilayah) {
          wilOK = true;
        }


        if (f_aktif=="ALL") {
          aktifOK = true;
        } else if (f_aktif=="YT") {
          if (ListDealer[i].AKTIF=="Y") { 
            aktifOK = true;
          } else if (ListDealer[i].AKTIF=="T") {
            aktifOK = true;
          }
        } else if (ListDealer[i].AKTIF==f_aktif) {
          aktifOK = true;
        }

        if (f_cbd=="ALL") {
          cbdOK = true;
        } else if (f_cbd=="Y" && ListDealer[i].STATUSCBD==1) {
          cbdOK = true;
        } else if (f_cbd=="N" && ListDealer[i].STATUSCBD==0) {
          cbdOK = true;
        }


        if (f_nama=="") {
          namaOK = true;
        } else if (((ListDealer[i].NM_PLG).toUpperCase()).includes(f_nama.toUpperCase())) {
          namaOK = true;
        }

        if (wilOK==true && aktifOK==true && cbdOK==true && namaOK==true) {
          append = true;
        }

        if (append==true) {
          NO = NO+1;
          var KDPLG = ListDealer[i].KD_PLG;

          var DEALER = "<b><font color='navy'>"+ListDealer[i].NM_PLG+"</font></b><br>"
          DEALER += "KODE : <b>"+KDPLG+"</b><br>";
          DEALER += "NAMA TOKO : <b>"+ListDealer[i].NM_TOKO+"</b><br>";
          DEALER += "EMAIL: <b>"+ListDealer[i].EMAIL_ADDRESS+"</b><br>";
          DEALER += "NO HP: <b>"+ListDealer[i].NO_HP+"</b><br>";
          DEALER += "NO VA: <b>"+((ListDealer[i].NO_VA=="")?"-":"75111"+ListDealer[i].NO_VA)+"</b>";

          var WILAYAH = ListDealer[i].WILAYAH+"<br>"+ListDealer[i].KOTA+"<br>";
          if (ListDealer[i].AKTIF=="T") {
            WILAYAH += "<button style='background-color:red;color:white;'>TERKUNCI</button>";
          }

          var CL = "CL MISHIRIN: <b>"+number_format(ListDealer[i].CL_MISHIRIN)+"</b><br>";
          CL += "CL CO&SANITARY: <b>"+number_format(ListDealer[i].CL_COSANITARY)+"</b><br>";
          CL += "CL SPAREPART: <b>"+number_format(ListDealer[i].CL_SPAREPART)+"</b><br>";

          var cbdOn  = "<input type='text' class='cbd-ONN-"+KDPLG+"' style='background-color:#e38400;color:white;text-align:center;width:100px;' value='CBD' readonly>";
          var cbdOff = "<input type='text' class='cbd-OFF-"+KDPLG+"' style='background-color:#0a0075;color:white;text-align:center;width:100px;' value='NOT CBD' readonly>";
          var reqOn  = "<input type='text' class='req-ONN-"+KDPLG+"' style='background-color:#75002d;color:white;text-align:center;width:175px;' value='CBD ON REQUESTED' readonly>";
          var reqOff = "<input type='text' class='req-OFF-"+KDPLG+"' style='background-color:#260440;color:white;text-align:center;width:175px;' value='CBD OFF REQUESTED' readonly>";

          var STATUS = "";
          //STATUS += cbdOn+cbdOff+reqOn+reqOff+"<br>";
          STATUS += "NPWP: <b>"+((ListDealer[i].NPWP=="")?"-":ListDealer[i].NPWP)+"</b><br>";
          STATUS += "JENIS PPH: <b>"+((ListDealer[i].JENIS_PPH=="")?"-":ListDealer[i].JENIS_PPH)+"</b><br>";

          var BUTTONS = "<button class='btnViewDealer' nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>VIEW DETAIL</button>";
          //BUTTONS += "<button class='btnCheckPiutang' nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>CEK PIUTANG</button>";
          //BUTTONS += "<button class='btnDeactivate btnDeactivate-"+KDPLG+"' nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>DEACTIVATE</button>";  
          //BUTTONS += "<button class='btnActivate btnActivate-"+KDPLG+"' nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>ACTIVATE</button>";  
          //BUTTONS += "<button class='btnCBDON  btnCBDON-"+KDPLG+"' nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>CBD ON</button>";
          //BUTTONS += "<button class='btnCBDOFF btnCBDOFF-"+KDPLG+"'  nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>CBD OFF</button>";
          //BUTTONS += "<button class='btnCANCEL btnCANCEL-"+KDPLG+"'  nourut="+NO+" kdplg='"+KDPLG+"' nmplg='"+ListDealer[i].NM_PLG+"'>CANCEL REQUEST CBD</button>";
        
          //var MOBILE = DEALER+"<br>"+WILAYAH+"<br>"+CL+"<br>"+STATUS+"<br>"+BUTTONS;
          var MOBILE = DEALER+"<br>"+WILAYAH+"<br>"+CL+"<br>"+BUTTONS;

          var BRS = "<tr id='dataDealer-"+KDPLG+"'>"; 
          BRS += "<td class='hideOnMobile'>"+NO+"</td>";
          BRS += "<td class='hideOnMobile'>"+DEALER+"</td>";
          BRS += "<td class='hideOnMobile'>"+WILAYAH+"</td>";
          BRS += "<td class='hideOnMobile'>"+CL+"</td>";
          //BRS += "<td class='hideOnMobile' id='status-"+KDPLG+"'>"+STATUS+"</td>";
          BRS += "<td class='hideOnMobile' id='button-"+KDPLG+"'>"+BUTTONS+"</td>";
          BRS += "<td class='colMobile' id='mobile-"+KDPLG+"'>"+MOBILE+"</td>";
          BRS += "</tr>";
          $("#tblDealerBody").append(BRS);

          if (ListDealer[i].AKTIF=="N") {
            $(".btnDeactivate-"+KDPLG).hide();
            $(".btnActivate-"+KDPLG).show();
          } else {
            $(".btnDeactivate-"+KDPLG).show();
            $(".btnActivate-"+KDPLG).hide();
          }

          if (ListDealer[i].REQUESTCBD=="") {
            $(".req-ONN-"+KDPLG).hide();
            $(".req-OFF-"+KDPLG).hide();
            $(".btnCANCEL-"+KDPLG).hide();

            if (ListDealer[i].STATUSCBD==0) {
              $(".btnCBDON-"+KDPLG).show();
              $(".btnCBDOFF-"+KDPLG).hide();
              $(".cbd-ONN-"+KDPLG).hide();
              $(".cbd-OFF-"+KDPLG).show();
            } else {
              $(".btnCBDON-"+KDPLG).hide();
              $(".btnCBDOFF-"+KDPLG).show();
              $(".cbd-ONN-"+KDPLG).show();
              $(".cbd-OFF-"+KDPLG).hide();
            }
          } else {
            $(".btnCANCEL-"+KDPLG).show();
            $(".btnCBDON-"+KDPLG).hide();
            $(".btnCBDOFF-"+KDPLG).hide();
            $(".cbd-ONN-"+KDPLG).hide();
            $(".cbd-OFF-"+KDPLG).hide();

            if (ListDealer[i].REQUESTCBD=="CBD ON") {
              $(".req-ONN-"+KDPLG).show();
              $(".req-OFF-"+KDPLG).hide();
            } else {
              $(".req-ONN-"+KDPLG).hide();
              $(".req-OFF-"+KDPLG).show();
            }
          }
        }

        if (i==(TotalDealer-1)) {
          $(".loading").hide();
        }
      }
  };

  $(document).ready(function(){
    $(".PopUpForm").hide();
    RefreshListDealer();
    activateDatepicker();

    $('.filter').on('change', function() {
      RefreshListDealer();
    });

    $(".btnViewDealer").click(function(){
      var KdPlg = $(this).attr("kdplg");
      var NmPlg = $(this).attr("nmplg");
      var PLG = "";

      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo($this->API_URL);?>/MasterDealer/GetDealer?api=APITES&plg="+KdPlg, {
        csrf_bit : csrf_bit
      }, function(data){
        if (data.result == "sukses") {
          var DEALER = data.data;
          PLG += "Kode: "+DEALER.KD_PLG+"<br>"; 
          PLG += "Nama: "+DEALER.NM_PLG+"<br>"; 
          PLG += "NamaToko: "+DEALER.NM_TOKO+"<br>"; 
          PLG += "Kode: "+DEALER.KD_PLG+"<br>"; 
          PLG += "Alamat: "+DEALER.ALM_PLG+"<br>"; 
          PLG += "Kota: "+DEALER.KOTA+"<br>"; 
          PLG += "Wilayah: "+DEALER.WILAYAH+"<br>"; 
          PLG += "Email: "+DEALER.EMAIL_ADDRESS+"<br>"; 
          PLG += "NoHP: "+DEALER.NO_HP+"<br>"; 
          PLG += "NPWP: "+DEALER.NPWP+"<br>"; 
          PLG += "KelompokPKP: "+DEALER.KELOMPOK_PKP+"<br>"; 
          PLG += "JenisPPH: "+DEALER.JENIS_PPH+"<br>"; 
          PLG += "NoVA: "+DEALER.NO_VA+"<br>";  

          $("#formPLGDetail").html(PLG);
    /*SELECT rtrim(kd_plg) as KD_PLG, rtrim(nm_plg) as NM_PLG, rtrim(alm_plg) AS ALM_PLG, rtrim(kota) AS KOTA, rtrim(npwp) AS NPWP, 
    rtrim(telp) AS TELP, rtrim(fax) AS FAX, rtrim(kontak_person) AS KONTAK_PERSON, credit_limit AS CREDIT_LIMIT, 
    termofpayment AS TERMOFPAYMENT, rtrim(wilayah) AS WILAYAH, total_penerimaanfaktur AS TOTAL_PENERIMAANFAKTUR, 
    total_pembgantung AS TOTAL_PEMBGANTUNG, total_pembayaran AS TOTAL_PEMBAYARAN, rtrim(ket) AS KET, 
    tempPiutang AS TERMPIUTANG, Piutang_awal AS PIUTANG_AWAL, Entry_time AS ENTRY_TIME, TransferCab AS TRANSFERCAB,
    rtrim([USER_NAME]) AS USER_NAME, Kd_Hari AS KD_HARI, rtrim(Aktif) AS AKTIF, rtrim(isnull(Kelompok_PKP,'')) AS KELOMPOK_PKP,
    rtrim(isnull(GRUP,'')) AS GRUP, MemberOF AS MEMBEROF, CL_Mishirin AS CL_MISHIRIN, CL_Trading AS CL_TRADING, CL_COSanitary AS CL_COSANITARY,
    rtrim(isnull(Alm_Plg2,'')) AS ALM_PLG2, rtrim(isnull(Marker,'')) AS MARKER, rtrim(isnull(Jenis_PPh,'')) AS JENIS_PPH, 
    rtrim(isnull(Nama_Bank,'')) AS NAMA_BANK, rtrim(isnull(Cabang_Bank,'')) AS CABANG_BANK,
    rtrim(isnull(No_Rekening,'')) AS NO_REKENING, rtrim(isnull(NamaPemilik_Rekening,'')) AS NAMAPEMILIK_REKENING, 
    rtrim(isnull(No_VA,'')) AS NO_VA, rtrim(isnull(Kd_SIC,'')) AS KD_SIC,
    rtrim(isnull(Email_Address,'')) AS EMAIL_ADDRESS, rtrim(isnull(No_HP,'')) AS NO_HP, rtrim(isnull(Nm_Toko,'')) AS NM_TOKO, 
    rtrim(isnull(Kd_Lokasi,(select Kd_Lokasi From TblConfig))) AS KD_LOKASI,
    'N' as STATUS_CL_MISHIRIN_TEMPORARY, 0 as CREDIT_LIMIT_MISHIRIN_PERMANEN,
    'N' as STATUS_CL_TRADING_TEMPORARY, 0 as CREDIT_LIMIT_TRADING_PERMANEN,
    'N' as STATUS_CL_COSAN_TEMPORARY, 0 as CREDIT_LIMIT_COSAN_PERMANEN, 
    Faktur1st as FAKTUR1ST*/
          $(".loading").hide();
        } else {
          $(".loading").hide();
        }
      },'json',errorAjax);   

      $("#FormPLG").show();
    });


    $('#CBDON_STARTDATE').on('change', function() {
      alert("SET TGL ON");
      $("#FormSetCBDON").show();
    });
    $('#CBDOFF_STARTDATE').on('change', function() {
      alert("SET TGL OFF");
      $("#FormSetCBDOFF").show();
    });

    $(".btnCBDON").click(function(){
      var KdPlg = $(this).attr("kdplg");
      var NmPlg = $(this).attr("nmplg");
      $("#FormSetCBDON #CBDON_KDPLG").val(KdPlg);
      $("#FormSetCBDON #CBDON_NMPLG").val(NmPlg);
      $("#FormSetCBDON").show();
    });

    $("#CBDON_BACK").click(function(){
      $(".PopUpForm").hide();
    });

    $("#CBDON_NEXT").click(function(){
      var KDPLG = $("#CBDON_KDPLG").val();
      var NMPLG = $("#CBDON_NMPLG").val();
      var STARTDATE = $("#CBDON_STARTDATE").val();

      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo site_url('MsDealer/CreateRequestCBD'); ?>", {
        KdPlg    : KDPLG,
        NmPlg    : NMPLG,
        Tgl      : STARTDATE,
        CBD      : 1,
        csrf_bit : csrf_bit
      }, function(data){
        if (data.result == "sukses") {
          alert("REQUEST PENGAKTIFAN STATUS CBD TERKIRIM");
          $("#FormSetCBDON").hide();
          $(".btnCBDON-"+KDPLG).hide();
          $(".btnCBDOFF-"+KDPLG).hide();
          $(".btnCANCEL-"+KDPLG).show();

          $(".cbd-ONN-"+KDPLG).hide();
          $(".cbd-OFF-"+KDPLG).hide();
          $(".req-ONN-"+KDPLG).show();
          $(".req-OFF-"+KDPLG).hide();

          $(".loading").hide();
        } else {
          alert("REQUEST PENGAKTIFAN STATUS CBD GAGAL TERKIRIM : "+data.error);
        }
      },'json',errorAjax);    
    });

    $(".btnCBDOFF").click(function(){
      var KdPlg = $(this).attr("kdplg");
      var NmPlg = $(this).attr("nmplg");
      $("#FormSetCBDOFF #CBDOFF_KDPLG").val(KdPlg);
      $("#FormSetCBDOFF #CBDOFF_NMPLG").val(NmPlg);
      activateDatepicker();
      $("#FormSetCBDOFF").show();
    });

    $("#CBDOFF_BACK").click(function(){
      $(".PopUpForm").hide();
    });

    $("#CBDOFF_NEXT").click(function(){
      var KDPLG = $("#CBDOFF_KDPLG").val();
      var NMPLG = $("#CBDOFF_NMPLG").val();
      var STARTDATE = $("#CBDOFF_STARTDATE").val();

      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo site_url('MsDealer/CreateRequest'); ?>", {
        KdPlg    : KDPLG,
        NmPlg    : NMPLG,
        Tgl      : STARTDATE,
        CBD      : 0,
        csrf_bit : csrf_bit
      }, function(data){
        if (data.result == "sukses") {
          alert("REQUEST PENONAKTIFAN CBD TERKIRIM");
          $("#FormSetCBDOFF").hide();
          $(".btnCBDON-"+KDPLG).hide();
          $(".btnCBDOFF-"+KDPLG).hide();
          $(".btnCANCEL-"+KDPLG).show();

          $(".cbd-ONN-"+KDPLG).hide();
          $(".cbd-OFF-"+KDPLG).hide();
          $(".req-ONN-"+KDPLG).hide();
          $(".req-OFF-"+KDPLG).show();

          $(".loading").hide();
        } else {
          alert("REQUEST PENONAKTIFAN CBD GAGAL TERKIRIM : "+data.error);
        }
      },'json',errorAjax);    
    });
  });
</script>  
<style>
  #PopUpPLG { height:800px;width:500px; }
  #formPLGTitle { font-weight: bold; font-size:14pt;}
@media only screen and (max-width: 960px) {
  #PopUpPLG { height:800px;width:500px; }
  #formPLGTitle { font-weight: bold; font-size:12pt;}
}
@media only screen and (max-width: 460px) {
  #PopUpPLG { height:600px!important;width:100%; overflow-y:scroll!important; padding:5px;}
  .formPopUp { width:100%!important; font-size:11px;}
  #formPLGTitle { font-weight: bold; font-size:10pt; margin-top:50px!important;}

}
</style>

  <div class="container">
      <div id="debug"></div>
      <div class="row" style="border:1px solid #ccc;border-radius:10px;margin-left:5px;margin-right:5px;margin-bottom:10px;background-color:#e8ff8a;">
        <div class="col-2 col-m-2">
          Wilayah<br>
          <select class="filter" id="f_wilayah" name="f_wilayah" style="height:24px;">
            <?php for($i=0;$i<count($ListWilayah);$i++) {
              echo("<option value='".$ListWilayah[$i]["WILAYAH"]."'".(($ListWilayah[$i]["WILAYAH"]==$DefaultWilayah)?" selected":"").">".$ListWilayah[$i]["WILAYAH"]."</option>");
            } ?>
          </select>
        </div>
        <div class="col-2 col-m-2">
          Status Aktif<br>
          <select class="filter" id="f_aktif" name="f_aktif" style="height:24px;">
            <option value="YT">AKTIF&TERKUNCI</option>
            <option value="Y">AKTIF</option>
            <option value="T">TERKUNCI</option>
            <option value="N">TIDAK AKTIF</option>
            <option value="ALL">ALL</option>
          </select>
        </div>
        <div class="col-1 col-m-1" style="display:none;">
          CBD<br>
          <select class="filter" id="f_cbd" name="f_cbd" style="height:24px;">
            <option value="ALL">ALL</option>
            <option value="Y">CBD</option>
            <option value="N">NOT CBD</option>
          </select>
        </div>
        <div class="col-3 col-m-3">
          Nama Dealer/Toko (tab u/ refresh)<br>
          <input type="text" class="filter" id="f_nama" name="f_nama" style="height:24px;width:240px;">
        </div>          
        <div class="col-4 col-m-4">
        </div>
      </div>
      <div>
        <table id="tblDealer" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="table">
          <?php 
            echo "<thead>";
            echo "  <tr>";
            echo "    <th class='hideOnMobile' width='5%'>No</th>";
            echo "    <th class='hideOnMobile' width='45%'>Dealer</th>";
            echo "    <th class='hideOnMobile' width='15%'>Wilayah/Kota</th>";
            echo "    <th class='hideOnMobile' width='25%'>CreditLimit</th>";
            //echo "    <th class='hideOnMobile' width='18%'>Status</th>";
            echo "    <th class='hideOnMobile' width='10%'>Action</th>";
            echo "    <th class='colMobile' width='100%'>Dealer</th>";
            echo "  </tr>";
            echo "</thead>";
            echo "<tbody id='tblDealerBody'>";
            echo "</tbody>"; 
            ?>
        </table>
      </div>
  </div> <!-- /container -->

  <div class="PopUpForm" id="FormPLG">
    <div class="overlay"></div>
    <div class="loadingItem">
      <div class="formPopUp formPopUp2" id="PopUpPLG" style="height:800px;width:500px;">
        <i class="fa fa-times ClosePopUp"></i>
        <div id="formPLGTitle">DETAIL DEALER</div>
        <div id="formPLGDetail">
        </div>
        <button id="formPLG_BACK">BACK</button>
      </div>
    </div>
  </div>

  <div class="PopUpForm" id="FormSetCBDON">
    <div class="overlay"></div>
    <div class="loadingItem">
      <div class="formPopUp formPopUp2" style="height:400px!important;width:500px!important;">
        <i class="fa fa-times ClosePopUp"></i>
        <h3>PENGAKTIFAN STATUS CBD</h3>
        <div style="padding:10px;">
          <div class="row">
            <div class="col-3 col-m-5">Kode Dealer:</div>
            <div class="col-3 col-m-5"><input type="text" id="CBDON_KDPLG" style="width:350px!important;"></div>
          </div>
          <div class="row">
            <div class="col-3 col-m-5">Nama Dealer:</div>
            <div class="col-3 col-m-5"><input type="text" id="CBDON_NMPLG" style="width:350px!important;"></div>
          </div>
          <div class="row">
            <div class="col-3 col-m-5">Tgl Start :</div>
            <div class="col-3 col-m-5"><input type='text' class='form-control datepicker datepickerInput' id='CBDON_STARTDATE' name='CBDOFF_STARTDATE' style='width:200px!important;z-index:3000;' required></div>
          </div>
          <div class="row">
            <div class="col-12 col-m-12">
              <b>WARNING!!! Saat Status CBD Aktif<br>
              Credit Limit Produk Dealer ini akan langsung diubah ke 0<br></b>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-m-12" align="right">
              <button id="CBDON_NEXT">REQUEST CBD ON</button>
              <button id="CBDON_BACK">BACK</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="PopUpForm" id="FormSetCBDOFF">
    <div class="overlay"></div>
    <div class="loadingItem">
      <div class="formPopUp formPopUp2" style="height:400px!important;width:500px!important;">
        <i class="fa fa-times ClosePopUp"></i>
        <h3>PENGNONAKTIFAN STATUS CBD</h3>
        <div style="padding:10px;">
          <div class="row">
            <div class="col-3 col-m-5">Kode Dealer:</div>
            <div class="col-3 col-m-5"><input type="text" id="CBDOFF_KDPLG" style="width:350px!important;"></div>
          </div>
          <div class="row">
            <div class="col-3 col-m-5">Nama Dealer:</div>
            <div class="col-3 col-m-5"><input type="text" id="CBDOFF_NMPLG" style="width:350px!important;"></div>
          </div>
          <div class="row">
            <div class="col-3 col-m-5">Tgl Start :</div>
            <div class="col-3 col-m-5"><input type='text' class='form-control datepicker datepickerInput' id='CBDOFF_STARTDATE' name='CBDOFF_STARTDATE' style='width:200px!important;z-index:3000;' required></div>
          </div>
          <div class="row">
            <div class="col-12 col-m-12">
              <b>WARNING!!! Dengan Mengnonaktifkan Status CBD<br>
              Credit Limit Produk Dealer ini akan dikalkulasi ulang berdasarkan Omzet Rata2 3 Bulan<br></b>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-m-12" align="right">
              <button id="CBDOFF_NEXT">REQUEST CBD OFF</button>
              <button id="CBDOFF_BACK">BACK</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
