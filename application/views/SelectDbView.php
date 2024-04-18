<?php
  if(!isset($_SESSION['logged_in'])){
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

    <title>MY COMPANY</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="<?php echo base_url('assets/css/ie10-viewport-bug-workaround.css');?>" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('dist/css/navbar-fixed-top.css');?>" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?php echo base_url('assets/js/ie-emulation-modes-warning.js');?>"></script>

    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>

    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>
    
  </head>

  <body>
    <!-- Fixed navbar -->
    <?php include ('template/menubar.php'); ?>

<?php
  $tampil=false;
  if ($tampil)
  {
?>
    <div class="container">
      <form method="post" action="creditLimitCtr/index">
        <div class="form-group">
          <label>Select Database</label>
          <select class="form-control" id="selDb" name="selDb">
            
          </select>
          <small id="info" class="form-text text-muted">Please select database to proceed</small>
        </div>
        <input type="submit" class="btn btn-primary" value="Proceed" id="btnProses">
      </form>
    </div> <!-- /container -->
<?php
  }
?>
    <script>
      $( document ).ready(function(){
          $.post("masterDb/getListDb",'',
            function(data){
              var sel = $("#selDb");
              sel.empty();
              for (var i=0; i<data.length; i++) {
                sel.append('<option value="' + data[i].ID + '">' + data[i].NamaDb + ' - ' + data[i].AlamatWebService + ' (Server : ' + data[i].Server + ', Database : ' + data[i].Database + ')</option>');
              }
            }, "json"
          );
      });
    </script>
  </body>
</html>
