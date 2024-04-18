<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('main','refresh');
  }
  // change here -- hardcode for database connection
  $username_db = 'sa';
  $password_db = 'Sprite12345';

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

    <title>Customer List | Credit Limit Application</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">
    <!-- <link href="<?php echo base_url('dist/css/datatables.bootstrap.min.css');?>" rel="stylesheet"> -->
    
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

    <!-- Bootstrap core Javascript -->
    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.bootstrap.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>


    <!-- untuk number formatting -->
    <script src="<?php echo base_url('dist/js/jquery.number.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.number.min.js');?>"></script>

    <!-- untuk date formatting -->
    <script src="<?php echo base_url('dist/js/jquery.dateformat.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.dateformat.min.js');?>"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>

    <script>
      $(document).ready(function() {
        $.ajax({
            // url : "http://<?php echo $row[0]->AlamatWebService;?>",
            url : "http://<?php echo $row[0]->AlamatWebService;?>/index.php/Controller/getCustomerList/",
            type: "POST",
            data: {hostname : '<?php echo $row[0]->Server; ?>', username : '<?php echo $username_db; ?>', password : '<?php echo $password_db; ?>', database : '<?php echo $row[0]->Database; ?>'},
            dataType: "JSON",
            success: function(data)
            {
              // alert(data.length);
              // alert(data[0]["nm_plg"]);
              $("#example tbody tr").remove();
              for(var i=0;i<data.length;i++)
              {

                  var kdplg = data[i]["kd_plg"].trim();
                  var nmplg = data[i]["nm_plg"].trim().replace(/ /g,"_");
                  var alplg = data[i]["alm_plg"].trim().replace(/ /g,"_");
                  var tr="<tr>";
                  var td0="<td><button onClick=sendValue('"+kdplg+"','"+nmplg+"','"+alplg+"')>Select</button></td>";
                  var td1="<td>"+(i+1)+"</td>";
                  var td2="<td>"+data[i]["kd_plg"]+"</td>";
                  var td3="<td>"+data[i]["nm_plg"]+"</td>";
                  var td4="<td>"+data[i]["alm_plg"]+"</td>";
                  
                  
                  $("#example tbody").append(tr+td0+td1+td2+td3+td4);
              }
              $('#example').DataTable();
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
              alert('Error get data from ajax');
            }
          });
      } );

      function sendValue(kd_plg,nm_plg,alm_plg)
      {
          var parentId = <?php echo json_encode($_GET['id']); ?>;
          window.opener.updateValue(parentId, kd_plg, nm_plg, alm_plg);
          window.close();
      }

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
        echo "<th>&nbsp</th>";  
        echo "<th>No</th>";
        // echo "<th>id</th>";
        echo "<th>Kode Pelanggan</th>";
        echo "<th>Nama Pelanggan</th>";
        echo "<th>Alamat Pelanggan</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        echo "</tbody>"; ?>
    </table>
    </div> <!-- /container -->

  </body>
</html>
