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

    <title>Login Bhakti.co.id</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('dist/css/navbar-fixed-top.css');?>" rel="stylesheet">

    <!-- new bootstrap 28 feb 2017 by Reo -->
    <link href="<?php echo base_url('assets/css/bootstrap.min.css');?>" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo base_url('assets/css/stylish-portfolio.css');?>" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('css/navbar-fixed-top.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('css/datetimepicker.css');?>" rel='stylesheet' type='text/css'>
    <link href="<?php echo base_url('css/mycompany.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('css/style.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('css/custom.css');?>" rel="stylesheet">

    <style>
    body {
      background-color:black;
      padding-top:0px!important;
    }
    .loginscreen {
      width:500px;
    }
    #header-box {
      background-color:black;
      font-size:25px;
      color:white;
      position:fixed;top:0px;left:0px;
      width:100%;
      height:35px;
    }
    .col-7, .col-5 {
      padding:0!important;
    }
    </style>
  </head>

  <body class="gray-bg" style="max-width:100%;">
    <div style="display:<?php if(!$this->session->flashdata('error')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="login-alert" class="alert alert-danger col-sm-12">
      <!-- error msg here -->
      <?php 
        echo $this->session->flashdata('error'); 
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
      ?>
    </div>
    <div style="display:<?php if(!$this->session->flashdata('info')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
      <?php 
        echo $this->session->flashdata('info'); 
        if(isset($_SESSION['info'])){
            unset($_SESSION['info']);
        }
      ?>
    </div>

    <!-- <div id="header-box" style="">
      <div>PT.BHAKTI IDOLA TAMA</div>
    </div> -->
    <div class="row">
      <div class="col-7">
        <img src="<?php echo base_url('images/shimizu.png');?>" width="100%"></img>
        <img src="<?php echo base_url('images/rinnai.png');?>" width="100%"></img>
      </div>
      <div class="col-5">
        <img src="<?php echo base_url('images/miyako_login.png');?>" width="100%"></img>
              <!-- <div class="container loginscreen"> -->
        <div class="loginscreen" style="position:absolute; z-index:1;top:85px;">
        <div id="loginbox" style="padding:30px;" class="mainbox">                    
          <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
            </div>     
            <div class="panel-body" >      
                <?php //echo form_open('LoginController/authZEN'); ?>            
                <?php echo form_open('LoginController'); ?>
                  <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                    <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="your useremail on www.bhakti.co.id" required="true">                                        
                  </div>
                      
                  <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                    <input id="login-password" type="password" class="form-control" name="password" placeholder="your password on www.bhakti.co.id" required="true">
                  </div>
                  <div style="margin-top:10px" class="form-group">
                    <!-- Button -->
                    <center>
                      <div class="col-sm-12 controls">
                        <input type="submit" class="btn btn-primary" value="Login">
                      </div>
                    </center>
                  </div>
                </form>     
                <div style="margin-top:25px!important;margin-bottom:25px!important; color:green; text-align:center; font-size:14px;">
                </div>
                <div style="margin-top:25px!important;margin-bottom:25px!important; color:green; text-align:center; font-size:14px;">
                    <a href="#" data-toggle='modal' data-target='#reset-password'><u>RESET PASSWORD</u></a>
                </div>
                <div style="width:50%; float: left;">
                  <a href="<?php echo base_url('MainController/changelog');?>" target="_blank">
                    View Change Log
                  </a>
                </div>
                <div style="width: 50%; float: left; color:black; text-align:right; font-size:10px;">v.<?php echo($version);?></div>
            </div>                     
          </div> <!-- loginbox -->
        </div> <!-- /container -->
      </div>
    </div>
    <div class="row" style="display:none;">
      PEMBERITAHUAN MAINTENANCE ZEN.BHAKTI.CO.ID
      RABU, 1 FEB 2023 08:30 - 09:30 WIB
      PENGAJUAN/APPROVAL CREDIT LIMIT SELAMA PERIODE MAINTENANCE TIDAK DAPAT DILAKUKAN
    </div>
    <div style="position:fixed;bottom:0px;left:0px;width:100%;height:60px;background-color:black;">
      <div style='color:white;font-size:12px;height:50px;line-height:60px;vertical-align:bottom;text-align:center;'>
        &copy; 2022 PT.Bhakti Idola Tama - All Rights Reserved. 
      </div>
    </div>

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

    <script type="text/javascript">
      setTimeout(function() {
          $('#login-alert').fadeOut('slow');
          $('#info-alert').fadeOut('slow');
      }, 3000);
    </script>
  </body>
</html>
