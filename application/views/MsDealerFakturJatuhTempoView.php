<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('main','refresh');
  }

  // change here -- hardcode for database connection
  $username_db = 'mishirin';
  $password_db = 'br4v01nd14T4n990';

  if(!isset($kd_slsman)){
    $kd_slsman = '';
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

      .dropdown-submenu {
          position: relative;
      }

      .dropdown-submenu .dropdown-menu {
          top: 0;
          left: 100%;
          margin-top: -1px;
      }
    </style>
  </head>

  <body>
    <!-- loading div -->
    <div id="disablingDiv" class="disablingDiv">
    </div>
    <div id="loading" class="loader">
    </div>
    <!-- end loading div -->


    <!-- Alert Model -->
    <div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
      <div style="padding: 5px;">
    <?php
        if (isset($_GET['requestsuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Email Request Sent <strong>Successfully !</strong></div>";
        }
    ?>
      </div>
    </div>

    <!-- Fixed navbar -->
    <?php include ('template/menubar.php'); ?>

    <div class="container">
      <div class="form-group">
          <label>WILAYAH</label>
          <select class="form-control" id="selWil" name="selWil" onchange="getListToko(this.value)">
          </select>
          <!-- <small id="info" class="form-text text-muted">Please Select Divisi</small> -->
        </div>
        <div class="form-group">
          <label>TOKO</label>
          <select class="form-control" id="selToko" name="selToko" onchange="">
          </select>
          <!-- <small id="info" class="form-text text-muted">Please Select Divisi</small> -->
        </div>
        <div class="form-group">
          <label>DIVISI</label>
          <select class="form-control" id="selDiv" name="selDiv" onchange="">
          </select>
          <!-- <small id="info" class="form-text text-muted">Please Select Divisi</small> -->
        </div>
        <div class="form-group">
          <label>STATUS</label>
          <select class="form-control" id="selSta" name="selSta" onchange="">
            <option value="ALL">ALL</option>
            <option value="SUDAH JATUH TEMPO">Sudah Jatuh Tempo</option>
            <option value="BELUM JATUH TEMPO">Belum Jatuh Tempo</option>
          </select>
          <!-- <small id="info" class="form-text text-muted">Please Select Divisi</small> -->
        </div>
        <div class="form-group" id="divJmlhHari">
          <label>JUMLAH TELAT HARI TELAT MINIMUM</label>
          <select class="form-control" id="selHar" name="selHar" onchange="">
            <option value="1">1</option>
            <option value="16">16</option>
            <option value="31">31</option>
            <option value="46">46</option>
            <option value="61">61</option>
          </select>
          <!-- <small id="info" class="form-text text-muted">Please Select Divisi</small> -->
        </div>
        <input type="button" class="btn btn-primary" value="Preview" id="btnSubmit" onclick="popupWindow('preview')">
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>

    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>

    <!-- untuk number formatting -->
    <script src="<?php echo base_url('dist/js/jquery.number.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.number.min.js');?>"></script>

    <!-- untuk date formatting -->
    <script src="<?php echo base_url('dist/js/jquery.dateformat.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.dateformat.min.js');?>"></script>

    <!-- untuk data table -->
    <script src="<?php echo base_url('dist/js/datatables.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.bootstrap.min.js');?>"></script>

    <script>
      $( document ).ready(function(){
        $("#flash-msg").delay(1200).fadeOut("slow");
        $("#loading").hide();
        $("#disablingDiv").hide();

        $("#divJmlhHari").hide();

        $('.dropdown-submenu a.test').on("click", function(e){
          $(this).next('ul').toggle();
          e.stopPropagation();
          e.preventDefault();
        });
        
        // ajax get wilayah
        $.ajax({
          url : "http://<?php echo $_SESSION['data']['row'][0]->AlamatWebService;?>/index.php/Controller/getListWilayah/",
          type: "POST",
          data: {hostname : '<?php echo $_SESSION['data']['row'][0]->Server; ?>', username : '<?php echo $username_db; ?>', password : '<?php echo $password_db; ?>', database : '<?php echo $_SESSION['data']['row'][0]->Database; ?>'},
          dataType: "JSON",
          success: function(data)
          {
            $("#selWil option").remove();
            $("#selWil").append("<option value='ALL'>ALL</option>");
            for(var i=0;i<data.length;i++)
            {
                var opt="<option value='"+data[i]["wilayah"]+"'>"+data[i]["wilayah"]+"</option>";
                $("#selWil").append(opt);

            }
          },
          error: function (jqXHR, textStatus, errorThrown)
          {
            alert('Error get data from ajax');
          }
        });
        // ajax get customer
        getListToko('ALL');

        // ajax get merk / divisi
        $.ajax({
          url : "http://<?php echo $_SESSION['data']['row'][0]->AlamatWebService;?>/index.php/Controller/getListMerk/",
          type: "POST",
          data: {hostname : '<?php echo $_SESSION['data']['row'][0]->Server; ?>', username : '<?php echo $username_db; ?>', password : '<?php echo $password_db; ?>', database : '<?php echo $_SESSION['data']['row'][0]->Database; ?>'},
          dataType: "JSON",
          success: function(data)
          {
            $("#selDiv option").remove();
            $("#selDiv").append("<option value='ALL'>ALL</option>");
            for(var i=0;i<data.length;i++)
            {
                var opt="<option value='"+data[i]["divisi"]+"'>"+data[i]["divisi"]+"</option>";
                $("#selDiv").append(opt);

            }
          },
          error: function (jqXHR, textStatus, errorThrown)
          {
            alert('Error get data from ajax');
          }
        });

      });

      $(document).ajaxStart(function() {
        $("#disablingDiv").show();
        $("#loading").show();
      });

      $(document).ajaxStop(function() {
        $("#loading").hide();
        $("#disablingDiv").hide();
      });


      function popupWindow(id){
         window.open('../CreditLimitCtr/PreviewFakturJT?id=' + encodeURIComponent(id) + '&wil='+$("#selWil").val().trim()+'&kdplg='+$("#selToko").val().trim()+'&div='+$("#selDiv").val().trim()+'&sta='+$("#selSta").val().trim()+'&har='+$("#selHar").val().trim()+'&kdsls=<?php echo $kd_slsman; ?>','popuppage',
        'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
      }
      function updateValue(id, kd_plg, nm_plg, alm_plg)
      {

      }

      function getListToko(wilayah){
        $.ajax({
          url : "http://<?php echo $_SESSION['data']['row'][0]->AlamatWebService;?>/index.php/Controller/getListToko/",
          type: "POST",
          data: {wilayah : wilayah, hostname : '<?php echo $_SESSION['data']['row'][0]->Server; ?>', database : '<?php echo $_SESSION['data']['row'][0]->Database; ?>'},
          dataType: "JSON",
          success: function(data)
          {
            $("#selToko option").remove();
            $("#selToko").append("<option value='ALL'>ALL</option>");
            for(var i=0;i<data.length;i++)
            {
                var opt="<option value='"+data[i]["kd_plg"]+"'>"+data[i]["nm_plg"]+" Kode : "+data[i]["kd_plg"]+"</option>";
                $("#selToko").append(opt);

            }
          },
          error: function (jqXHR, textStatus, errorThrown)
          {
            alert('Error get data from ajax');
          }
        });
      }

      $("#selSta").change(function(){
        if(this.value == "SUDAH JATUH TEMPO"){
          $("#divJmlhHari").show();
        }
        else{
          $("#divJmlhHari").hide();
        }
      });
 
    </script>
  </body>
</html>
