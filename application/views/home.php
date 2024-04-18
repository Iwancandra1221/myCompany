<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('MainController','refresh');
  }

  if(!isset($_SESSION["logged_in"]["userid"])){
    redirect("MainController",'refresh');
  }

  $goto1 = site_url()."Home/callHome/".urlencode($_SESSION['logged_in']['useremail'])."/".urlencode($_SESSION["logged_in"]["userid"]);
  $goto2 = "http://".$_SERVER['HTTP_HOST']."/UserRequest/Home/callHome/".urlencode($_SESSION['logged_in']['useremail'])."/".urlencode($_SESSION["logged_in"]["userid"]);
  $goto3 = "";
  if (WEBTITLE!="REPORT BHAKTI" && (strtoupper(BUGSNAG_RELEASE_STAGE)=="PRODUCTION" || strtoupper(BUGSNAG_RELEASE_STAGE)=="STAGING")) {
    $goto3 = "http://".$_SERVER['HTTP_HOST']."/reportWeb/Home/callHome/".urlencode($_SESSION['logged_in']['useremail'])."/".urlencode($_SESSION["logged_in"]["userid"]);
  } 
  $zen = "http://zen.bhakti.co.id";
  $mon = "http://".$_SERVER['HTTP_HOST']."/BhaktiVA/Login/autoLogin/".urlencode($_SESSION["logged_in"]["userid"]);
  $tant = "http://".$_SERVER['HTTP_HOST']."/TandaTerima/Login/autoLogin/".urlencode(md5($_SESSION['logged_in']['userid']));

  $allow_webhrd = false;
  $allow_mycompany = true;
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

    <title>Select Web Application to Enter</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('dist/css/navbar-fixed-top.css');?>" rel="stylesheet">


    <style type="text/css">
      .glyphicon {
          font-size: 75px;
      }
      .btn-hrd {
        background-color:#ff6666;
        color:white;
      }

      .btn-pm {
        background-color:#676b76;
      }
      .btn-pm:hover {
        background-color:#666666;
      }
      .colMobile { display:none; }

      @media only screen and (max-width: 460px) {
          /* For mobile phones: */
          .colMobile { display:block; padding: 1px 10px 2px 10px !important; }
          .hideOnMobile { display: none; }
      }
    </style>
  </head>

  <body>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Hi, Welcome <?php echo $_SESSION['logged_in']['username'];?> [USERID : <b><?php echo($_SESSION["logged_in"]["userid"]);?></b>]</a>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#" data-toggle='modal' data-target='#change-password'>Change Password</a></li>
            <li><a href="HomeController/logout">Sign out</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>



    <div class="container">
      <h4 style="display:<?php if(!$this->session->flashdata('error')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="login-alert" class="alert alert-danger col-sm-12">
      <!-- error msg here -->
      <?php 
        echo $this->session->flashdata('error'); 
        if(isset($_SESSION['error'])){
            unset($_SESSION['error']);
        }
      ?>
      </h4>
      <h4 style="display:<?php if(!$this->session->flashdata('info')) echo 'none';?>; position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;" id="info-alert" class="alert alert-success col-sm-12">
      <!-- error msg here -->
      <?php 
        echo $this->session->flashdata('info'); 
        if(isset($_SESSION['info'])){
            unset($_SESSION['info']);
        }
      ?>
      </h4>
      <!-- <marquee behavior="scroll" direction="right" scrollamount="10"> -->
      <h2 class="hideOnMobile">Please select one of the Web-based Application to Continue ...</h2>
      <!-- </marquee> -->
      <div class="colMobile" style="height:60px;"></div>


        <a href="<?php echo $goto1; ?>" class="btn btn-hrd btn-lg" style="width: 250px; height: 200px; padding-top:25px; background-color:#ebad05!important;" target="_blank">
          <img src="<?php echo(base_url().'/images/reports.png');?>" width="125" height="125">
          <br><b>MY COMPANY</b>
        </a>
        <div class="colMobile" style="height:10px;"></div>
      <?php 
        //if(in_array('DEVELOPER', $_SESSION['user_role']) or in_array('IT', $_SESSION['user_role']) or in_array('REPORT (MYCOMPANY)', $_SESSION['user_role'])){
        if (WEBTITLE!="PT.BHAKTI IDOLA TAMA" && (strtoupper(BUGSNAG_RELEASE_STAGE)=="PRODUCTION" || strtoupper(BUGSNAG_RELEASE_STAGE)=="STAGING")) {
      ?>        
        <a href="<?php echo $goto3; ?>" class="btn btn-hrd btn-lg" style="width: 250px; height: 200px; padding-top:25px; background-color:#ebad05!important;" target="_blank">
          <img src="<?php echo(base_url().'/images/reports.png');?>" width="125" height="125">
          <!--<span class="glyphicon glyphicon-home"></span>--><br><b>ReportWeb</b>
        </a>
        <div class="colMobile" style="height:10px;"></div>
      <?php
        }
      ?>

      <!-- <a href="<?php echo $goto2; ?>" class="btn btn-hrd btn-lg" style="width: 250px; height: 200px; padding-top:25px; background-color:#01941e !important;" target="_blank">
        <img src="<?php echo(base_url().'/images/userrequest2.png');?>" width="125" height="125"><br><b>USER REQUEST</b>
      </a>
      <div class="colMobile" style="height:10px;"></div> -->

        <a href="<?php echo $mon; ?>" class="btn btn-hrd btn-lg" style="width: 250px; height: 200px; background-color:#79d4c2!important;" target="_blank">
          <img src="<?php echo(base_url().'/images/bca-va.png');?>" width="140" height="140"><br><b>MONITORING VA</b>
        </a>

        <a href="<?php echo $zen; ?>" class="btn btn-hrd btn-lg" style="width: 250px; height: 200px; padding-top:50px; background-color:#93aba9!important;" target="_blank">
          <img src="<?php echo(base_url().'/images/zen.png');?>" width="70" height="70" style="margin-bottom:30px;">
          <br><b>Zen HRS</b>
        </a>

        <a href="<?php echo $tant; ?>" class="btn btn-hrd btn-lg" style="width: 250px; height: 200px; margin-top: 5px; background-color:#b97a57!important;" target="_blank">
          <img src="<?php echo(base_url().'/images/tanda_terima.png');?>" width="140" height="140"><br><b>TANDA TERIMA</b>
        </a>


        <div class="colMobile" style="height:10px;"></div>


      <?php 
        $goto5 = "http://".$_SERVER['HTTP_HOST']."/project-manager/authLogin/".urlencode($_SESSION['logged_in']['useremail']);
        $allow_project_manager = false;

        if(isset($_SESSION['logged_in']['loginProjectManager'])){
          $allow_project_manager = true;
        }

        if ($allow_project_manager) {
      ?>
          <a href="<?php echo $goto5; ?>" class="btn btn-info btn-lg btn-pm" style="width: 250px; height: 200px; padding-top:50px;" target="_blank">
            <span class="glyphicon glyphicon-briefcase"></span> Project Manager
          </a>
        <!-- <a href="http://mycompany.id/test/test.php" target="blank_">test</a> -->
      <?php
        } 
      ?>
    </div> <!-- /container -->

     <!-- modal change password -->
    <div class="modal fade" id="change-password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Change Password
            </div>
            <?php echo form_open('HomeController/changePassword'); ?>
            <div class="modal-body">
              <div class="form-group">
                Old Password
                <input type="password" class="form-control" name="txtOldPassword" id="txtOldPassword" placeholder="Please type your old password here" required>
              </div>
              <div class="form-group">
                New Password
                <input type="password" class="form-control" name="txtNewPassword" id="txtNewPassword" placeholder="Please type your new password here" required onkeyup="checkPasswordLength()" onchange="checkPassword()">
              </div>
              <div class="form-group">
                Re-type New Password
                <input type="password" class="form-control" name="txtReNewPassword" id="txtReNewPassword" placeholder="Please re-type your new password here" required onkeyup="checkPassword()" onchange="checkPassword()">
              </div>
            </div>
            
            <div class="modal-footer">
              <center><span style="color:red;" id="errormsg"></span></center>
              <input type="submit" class="btn btn-primary btn-ok" value="Confirm" id="btnChange">
              <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel">  
            </div>
            <?php
              if(!empty($err)){
            ?>
                <div class="bg-danger text-center" style="padding-top: 5px; padding-bottom:5px;">
                  Wrong old password.
                </div>
            <?php
              }
            ?>
            </form>
        </div>
    </div>
    </div>
    <!-- end modal change password -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>

    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>

    <script>
      function checkPasswordLength(){
        if ($('#txtNewPassword').val().length < 6){
          $('#errormsg').show();
          $('#errormsg').html("Your New Password is too short");
          $('#btnChange').prop('disabled', true);
          $('#txtReNewPassword').prop('disabled', true);
        }
        else{
          $('#errormsg').hide();
          $('#txtReNewPassword').prop('disabled', false);
        }
      }

      function checkPassword(){
        if ($('#txtNewPassword').val() != $('#txtReNewPassword').val()) {
          $('#errormsg').show();
          $('#errormsg').html("Your New Passwords do not match!");
          $('#btnChange').prop('disabled', true);
        }
        else{
          $('#errormsg').hide();
          $('#btnChange').prop('disabled', false);
        }
      }
    </script>
  </body>
</html>
<?php
  if(!empty($err)){
?>
      <script type="text/javascript">
        $(document).ready(function(){
          $('#change-password').modal('show');
          document.getElementById('login-alert').style.display='none';
        });
      </script>
<?php
  }
?>