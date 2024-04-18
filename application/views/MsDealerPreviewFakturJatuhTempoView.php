<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('main','refresh');
  }
  // change here -- hardcode for database connection
  $username_db = 'mishirin';
  $password_db = 'br4v01nd14T4n990';
  // echo $wilayah;
  // echo $toko;
  // echo $divisi;
  // echo $status;
  // echo $hari;
  $wilayah = $_GET['wil'];
  $toko = $_GET['kdplg'];
  $divisi = $_GET['div'];
  $status = $_GET['sta'];
  $hari = $_GET['har'];
  $kd_slsman = $_GET['kdsls'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Rekap Faktur Jatuh Tempo | Credit Limit Application</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="<?php echo base_url('assets/css/ie10-viewport-bug-workaround.css');?>" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('dist/css/navbar-fixed-top.css');?>" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?php echo base_url('assets/js/ie-emulation-modes-warning.js');?>"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- css for loading -->
    <style type="text/css">
      .disablingDiv{
        z-index:1;
         
        /* make it cover the whole screen */
        position: fixed; 
        top: 0%; 
        left: 0%; 
        width: 100%; 
        height: 100%; 
        overflow: hidden;
        margin:0;
        /* make it white but fully transparent */
        background-color: white; 
        opacity:0.5;  
      }
      .loader {
          position: absolute;
          left: 50%;
          top: 50%;
          z-index: 1;
          width: 150px;
          height: 150px;
          margin: -75px 0 0 -75px;
          border: 16px solid #f3f3f3;
          border-radius: 50%;
          border-top: 16px solid #3498db;
          width: 120px;
          height: 120px;
          -webkit-animation: spin 2s linear infinite;
          animation: spin 2s linear infinite;
      }

      @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
      }
    </style>
    <!--  -->

    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>

    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>

    <!-- utk data table -->
    <script src="<?php echo base_url('dist/js/datatables.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.bootstrap.min.js');?>"></script>

    <!-- untuk number formatting -->
    <script src="<?php echo base_url('dist/js/jquery.number.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.number.min.js');?>"></script>

    <!-- untuk date formatting -->
    <script src="<?php echo base_url('dist/js/jquery.dateformat.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.dateformat.min.js');?>"></script>

    <script>
      $(document).ready(function() {
        $("#loading").hide();
        $("#disablingDiv").hide();
        $.ajax({
            url : "http://<?php echo $_SESSION['data']['row'][0]->AlamatWebService;?>/index.php/Controller/getFakturJT/",
            type: "POST",
            data: {kd_slsman : '<?php echo $kd_slsman; ?>', wilayah : '<?php echo $wilayah; ?>', toko : '<?php echo $toko; ?>', divisi : '<?php echo $divisi; ?>', status : '<?php echo $status; ?>', hari : <?php echo $hari; ?>, hostname : '<?php echo $_SESSION['data']['row'][0]->Server; ?>', database : '<?php echo $_SESSION['data']['row'][0]->Database; ?>'},
            dataType: "JSON",
            success: function(data)
            {
              // alert(data.length);
              // alert(data[0]["nm_plg"]);
              $("#example tbody tr").remove();
              var bariskosong = "<tr><td colspan='9'>&nbsp;</td></tr>"
              var coufktgantung = 0;
              var totfktgantung = 0;
              var totsisfkt = 0;

              var wil_coufktgantung = 0;
              var wil_totfktgantung = 0;
              var wil_totsisfkt = 0;
              for(var i=0;i<data.length;i++)
              {
                  // cetak total per toko
                  if(i==0){
                    var currtoko = data[i]["kd_plg"];
                    coufktgantung = coufktgantung + 1;
                    totfktgantung = parseInt(totfktgantung) + parseInt(data[i]["grandtotal"]);
                    totsisfkt = parseInt(totsisfkt) + parseInt(data[i]["sisa_faktur"]);

                    var currwil = data[i]["wilayah"];
                  }
                  else{
                    if(currtoko != data[i]["kd_plg"]){
                      var tr = "<tr>";
                      var tdtotal = "<td>&nbsp;</td><td colspan='2'><strong>Total Faktur Gantung</strong></td><td><strong>"+$.number(coufktgantung)+"</strong></td><td colspan='3'><strong>Grand Total Toko</strong></td><td><strong>"+$.number(totfktgantung)+"</strong></td><td><strong>"+$.number(totsisfkt)+"</strong></td>";
                      var trn = "</tr>";
                      $("#example tbody").append(tr+tdtotal+trn);
                      $("#example tbody").append(bariskosong);
                      currtoko = data[i]["kd_plg"];
                      coufktgantung = 1;
                      totfktgantung = parseInt(data[i]["grandtotal"]);
                      totsisfkt = parseInt(data[i]["sisa_faktur"]);
                      if(currwil == data[i]["wilayah"]){
                        wil_coufktgantung = wil_coufktgantung + 1;
                      }
                    }
                    else{
                      coufktgantung = coufktgantung + 1;
                      totfktgantung = parseInt(totfktgantung) + parseInt(data[i]["grandtotal"]);
                      totsisfkt = parseInt(totsisfkt) + parseInt(data[i]["sisa_faktur"]);
                    }
                  }
                  // cetak total wilayah
                  if(i==0){
                    wil_coufktgantung = wil_coufktgantung + 1;
                    wil_totfktgantung = parseInt(wil_totfktgantung) + parseInt(data[i]["grandtotal"]);
                    wil_totsisfkt = parseInt(wil_totsisfkt) + parseInt(data[i]["sisa_faktur"]);
                  }
                  else{
                    if(currwil != data[i]["wilayah"]){
                      var tr = "<tr>";
                      var tdtotal = "<td>&nbsp;</td><td colspan='2'><strong>Total Toko Ada Faktur Gantung</strong></td><td><strong>"+$.number(wil_coufktgantung)+"</strong></td><td colspan='3'><strong>Grand Total Wilayah</strong></td><td><strong>"+$.number(wil_totfktgantung)+"</strong></td><td><strong>"+$.number(wil_totsisfkt)+"</strong></td>";
                      var trn = "</tr>";
                      $("#example tbody").append(tr+tdtotal+trn);
                      $("#example tbody").append(bariskosong);
                      $("#example tbody").append(bariskosong);
                      currwil = data[i]["wilayah"];
                      wil_coufktgantung = 1;
                      wil_totfktgantung = parseInt(data[i]["grandtotal"]);
                      wil_totsisfkt = parseInt(data[i]["sisa_faktur"]);
                    }
                    else{
                      wil_totfktgantung = parseInt(wil_totfktgantung) + parseInt(data[i]["grandtotal"]);
                      wil_totsisfkt = parseInt(wil_totsisfkt) + parseInt(data[i]["sisa_faktur"]);
                    }
                  }


                  var tr="<tr>";
                  if(i>0 && data[i]["wilayah"] == data[i-1]["wilayah"]){
                    var td="<td>&nbsp;</td>";
                  }
                  else{
                    var td="<td><strong>"+data[i]["wilayah"]+"</strong></td>";
                  }
                  
                  if(i>0 && data[i]["kd_plg"] == data[i-1]["kd_plg"]){
                    var td0="<td>&nbsp;</td>";
                  }
                  else{
                    var td0="<td><strong>"+data[i]["nm_plg"]+"</strong></td>";
                  }

                  var td1="<td>"+$.format.date(data[i]["tgl_faktur"], "dd-MMM-yyyy")+"</td>";
                  var td2="<td>"+data[i]["no_faktur"]+"</td>";
                  var td3="<td>"+data[i]["divisi"]+"</td>";
                  var td4="<td>"+$.format.date(data[i]["tgl_jatuhtempo"], "dd-MMM-yyyy")+"</td>";
                  var td5="<td>"+data[i]["lama_telat"]+"</td>";
                  var td6="<td>"+$.number(data[i]["grandtotal"])+"</td>";
                  var td7="<td>"+$.number(data[i]["sisa_faktur"])+"</td>";
                  
                  var trn="</tr>";
                  
                  $("#example tbody").append(tr+td+td0+td1+td2+td3+td4+td5+td6+td7+trn);
              }
              var tr = "<tr>";
              var tdtotal = "<td>&nbsp;</td><td colspan='2'><strong>Total Faktur Gantung</strong></td><td><strong>"+$.number(coufktgantung)+"</strong></td><td colspan='3'><strong>Grand Total Toko</strong></td><td><strong>"+$.number(totfktgantung)+"</strong></td><td><strong>"+$.number(totsisfkt)+"</strong></td>";
              var trn = "</tr>";
              $("#example tbody").append(tr+tdtotal+trn);
              $("#example tbody").append(bariskosong);

              var tr = "<tr>";
              var tdtotal = "<td>&nbsp;</td><td colspan='2'><strong>Total Toko Ada Faktur Gantung</strong></td><td><strong>"+$.number(wil_coufktgantung)+"</strong></td><td colspan='3'><strong>Grand Total Wilayah</strong></td><td><strong>"+$.number(wil_totfktgantung)+"</strong></td><td><strong>"+$.number(wil_totsisfkt)+"</strong></td>";
              var trn = "</tr>";
              $("#example tbody").append(tr+tdtotal+trn);
              $("#example tbody").append(bariskosong);
              $("#example tbody").append(bariskosong);

            },
            error: function (jqXHR, textStatus, errorThrown)
            {
              alert('Error get data from ajax');
            }
        });
        
      } );

      $(document).ajaxStart(function() {
        $("#disablingDiv").show();
        $("#loading").show();
      });

      $(document).ajaxStop(function() {
        $("#loading").hide();
        $("#disablingDiv").hide();
      });
    </script>
  </head>

  <body>
    <!-- loading div -->
    <div id="disablingDiv" class="disablingDiv">
    </div>
    <div id="loading" class="loader">
    </div>
    <!-- end loading div -->

    <div class="container">
      <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>Wilayah</th>";
        echo "<th>Nama Toko</th>";
        echo "<th>Tanggal Faktur</th>";  
        echo "<th>No Faktur</th>";
        echo "<th>Divisi</th>";
        echo "<th>Tanggal Jatuh Tempo</th>";
        echo "<th>Sisa Hari</th>";
        echo "<th>Total Faktur</th>";
        echo "<th>Sisa Faktur</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        echo "</tbody>"; ?>
    </table>
    </div> <!-- /container -->

  </body>
</html>
