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

    <title>Set Your New Password</title>

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
  </head>

  <body>
    <div style="display:<?php if(!isset($_SESSION['error'])) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="login-alert" class="alert alert-danger col-sm-12">
      <!-- error msg here -->
      <?php 
        echo $this->session->flashdata('error'); 
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
      ?>
    </div>
    <div style="display:<?php if(!isset($_SESSION['info'])) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
      <!-- error msg here -->
      <?php 
        echo $this->session->flashdata('info'); 
        if(isset($_SESSION['info'])){
            unset($_SESSION['info']);
        }
      ?>
    </div>
    <div class="container">
      <div id="loginbox" style="margin-top:20px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
        <div class="panel panel-info" >
          <div class="panel-heading">
              <div class="panel-title">Set New Password</div>
              <!-- <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#" data-toggle='modal' data-target='#reset-password'>Forgot password?</a></div> -->
          </div>     

          <div style="padding-top:30px" class="panel-body" >
                  
              <?php echo form_open('resetpassword/postnewpass'); ?>            
                <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                  <input id="login-username" type="text" class="form-control" name="useremail" value="<?php echo $_SESSION['logged_in']['useremail'];?>" placeholder="Your email address" required="true" readonly="true">                                        
                </div>

                <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                  <input id="login-password" type="password" class="form-control" name="password" placeholder="Your new password" required="true">
                </div>

                <div style="margin-bottom: 25px" class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                  <input id="login-password" type="password" class="form-control" name="repassword" placeholder="Re-type your new password" required="true">
                </div>

                <div style="margin-top:10px" class="form-group">
                  <!-- Button -->
                  <center>
                    <div class="col-sm-12 controls">
                      <input type="submit" class="btn btn-primary" value="Submit">
                    </div>
                  </center>
                </div>

              </form>     
            </div>                     
        </div> <!-- loginbox -->

    </div> <!-- /container -->

    <!-- modal reset password -->
    <div class="modal fade" id="reset-password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Reset Password
            </div>
            <?php echo form_open('resetpassword'); ?>
            <div class="modal-body">
              <div class="form-group">
                <input type="text" class="form-control" name="txtResetPassEmail" id="txtResetPassEmail" placeholder="Please input your registered email address here" required>
              </div>
            </div>
            <div class="modal-footer">
              <input type="submit" class="btn btn-primary btn-ok" value="Confirm">
              <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">  
            </div>
            </form>
        </div>
    </div>
  </div>
    <!-- end modal -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>

    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>

    <script type="text/javascript">
      setTimeout(function() {
          $('#login-alert').fadeOut('slow');
          $('#info-alert').fadeOut('slow');
      }, 3000);
    </script>
  </body>
</html>
