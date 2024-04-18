<?php
  if(!isset($_SESSION['logged_in']) or $access->can_read != 1){
    redirect('main','refresh');
  }

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

    <title>Laporan Stock Quick Count | MyCompany</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('dist/css/datatables.bootstrap.min.css');?>" rel="stylesheet">

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
    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.bootstrap.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>

    <!-- datepicker bootstrap & js -->
    <link href="<?php echo base_url('assets/datepicker/css/bootstrap-datepicker.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/datepicker/css/bootstrap-datepicker.min.css');?>" rel="stylesheet">
    <script src="<?php echo base_url('assets/datepicker/js/bootstrap-datepicker.js');?>"></script>
    <script src="<?php echo base_url('assets/datepicker/js/bootstrap-datepicker.min.js');?>"></script>

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

  </head>

  <body>
    <!-- loading div -->
    <div id="disablingDiv" class="disablingDiv">
    </div>
    <div id="loading" class="loader">
    </div>
    <!-- end loading div -->

    <!-- flash message -->
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

          if (isset($_GET['generateerror'])) {
            echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Generate Data Error !".$_GET['generateerror']."</strong></div>";
          }
      ?>
      </div>
    </div>


    <!-- Fixed navbar -->
    <?php //include ('template/menubar.php'); ?>

    <div class="container">
    <!-- <a href="../masterDbCtr">Cancel</a> -->
    
      <form action="StockQuickCount/Preview" method="post" target="_blank">
        <div class="form-group">
          <label>Tanggal</label>
          <div class="input-group date">
            <input type="text" class="form-control" id="dpDate" placeholder="yyyy-mm-dd" name="dpDate" required>
            <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Merk</label>
          <select id="selMerk" name="selMerk" class="form-control">
            <option value='ALL' selected>--SEMUA MERK--</option>
            <?php
              for($i=0;$i<count($merk);$i++){
                echo "<option value='".$merk[$i]->merk."'>".$merk[$i]->merk."</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Jenis Barang</label>
          <select id="selJenisBarang" name="selJenisBarang" class="form-control">
            <option value='ALL' selected>--SEMUA JENIS BARANG--</option>
            <?php
              for($i=0;$i<count($jenis_barang);$i++){
                echo "<option value='".$jenis_barang[$i]->Jns_Brg."'>".$jenis_barang[$i]->Jns_Brg."</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Kode Barang</label>
          <select id="selKodeBarang" name="selKodeBarang" class="form-control">
            <option value='ALL' selected>--SEMUA KODE BARANG--</option>
            <?php
              for($i=0;$i<count($kode_barang);$i++){
                echo "<option value='".$kode_barang[$i]->kd_brg."'>".$kode_barang[$i]->kd_brg."</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Wilayah</label>
          <select id="selWilayah" name="selWilayah" class="form-control">
            <option value='ALL' selected>--SEMUA WILAYAH--</option>
            <?php
              for($i=0;$i<count($wilayah);$i++){
                echo "<option value='".$wilayah[$i]->wilayah."'>".$wilayah[$i]->wilayah."</option>";
              }
            ?>
          </select>
        </div>
      
        <input type="submit" class="btn btn-primary" value="Preview Data" id="cmdPreviewData" name="cmdPreviewData">
        <!-- <input type="submit" class="btn btn-warning" value="Preview Data Compare Tahun Lalu" id="cmdPreviewDataCompare" name="cmdPreviewDataCompare"> -->
      </form> 
      <!--
      <label>No Faktur</label>
      <table id="tblFaktur" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <?php 
          echo "<thead>";
          echo "<tr>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";

          echo "</tbody>"; ?>
      </table>
      -->
    </div> <!-- /container -->

  </body>
  <script>
    $(document).ready(function() {

      $('#dpDate').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayBtn: "linked"
      });

      $("#dpDate").val(formatDate(new Date()));

      $('#dp2').datepicker({
        format: "yyyy-mm",
        autoclose: true,
        viewMode: "months", 
        minViewMode: "months"
      });

      $('#example').DataTable({
        "pageLength": 25
      });
      $("#flash-msg").delay(1200).fadeOut("slow");

      $("#txtTujuan").on('click', function(e){
        popupWindow('picktujuan');
      });

      $("#chkAllTujuan").attr("checked",true);
      $("#txtTujuan").hide();

      $("#radLokImp").attr("checked",true);
      $("#radN").attr("checked",true);

      $("#loading").hide();
      $("#disablingDiv").hide();

    } );

    $(document).ajaxStart(function() {
      $("#disablingDiv").show();
      $("#loading").show();
    });

    $(document).ajaxStop(function() {
      $("#loading").hide();
      $("#disablingDiv").hide();
    });

    function formatDate(date) {
      var monthNames = [
        "January", "February", "March",
        "April", "May", "June", "July",
        "August", "September", "October",
        "November", "December"
      ];
      
      date.setDate(date.getDate()-1);

      var day = date.getDate();
      var monthIndex = date.getMonth();
      var year = date.getFullYear();

      var month = monthIndex + 1;

      return year + '-' + month + '-' + day;
    }
  </script>
</html>