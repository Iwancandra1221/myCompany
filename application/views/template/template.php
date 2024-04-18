<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]> class="no-js"<!--> 
<html> 
<!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <title><?php echo $title; ?></title> -->
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="">
    <meta name="facebook-domain-verification" content="wnvl4umnsbenlt3q0qbmqiivfn4td1" />
    <link rel="icon" href="../../favicon.ico">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    
    <!--<link href="<?php echo base_url()?>js/vendor/datatable/css/jquery.dataTables.css" rel="stylesheet">-->
    <!--<script src="<?php echo base_url()?>js/vendor/datatable/jquery.dataTables.min.js"></script>-->    
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css"> -->
    <!-- <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> -->
    <script src="<?php echo base_url()?>js/moment-with-locales.js"></script>
    <link href="<?php echo base_url('js/vendor/datatable/css/jquery.dataTables.css')?>" rel="stylesheet">
    <script src="<?php echo base_url()?>js/vendor/datatable/jquery.dataTables.min.js"></script>


    <script src="<?php echo base_url()?>js/vendor/jquery.datetimepicker.js"></script>
    <script src="<?php echo base_url()?>js/vendor/jquery.inputmask.bundle.js"></script>
    <script src="<?php echo base_url()?>js/vendor/phpjs.js"></script>
    <script src="<?php echo base_url()?>js/lib.js"></script>
    <script src="<?php echo base_url()?>js/main.js"></script>

    <!-- Bootstrap -->
    <link href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
    <script src="<?php echo base_url('assets/bootstrap/js/bootstrap.min.js');?>"></script>
    <link href="<?php echo base_url('assets/bootstrap/css/datatables.bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
    <script src="<?php echo base_url('assets/bootstrap/js/datatables.bootstrap.min.js');?>"></script>
    <script src="<?php echo base_url('assets/bootstrap/js/datatables.min.js');?>"></script>

    <!-- datepicker bootstrap & js -->
    <link href="<?php echo base_url('assets/datepicker/css/bootstrap-datepicker.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/datepicker/css/bootstrap-datepicker.min.css');?>" rel="stylesheet">
    <script src="<?php echo base_url('assets/datepicker/js/bootstrap-datepicker.js');?>"></script>
    <script src="<?php echo base_url('assets/datepicker/js/bootstrap-datepicker.min.js');?>"></script>

    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/select2/css/select2.min.css">
    <script src="<?=base_url()?>assets/select2/js/select2.min.js"></script>
    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('css/navbar-fixed-top.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('css/datetimepicker.css');?>" rel='stylesheet' type='text/css'>
    <link href="<?php echo base_url('css/mycompany.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('css/style.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('css/custom.css');?>" rel="stylesheet">
    <style>
    </style>
  </head>

<?php
  $CI =& get_instance();
  date_default_timezone_set('Asia/Jakarta');

  if (isset($_SESSION["logged_in"])) {
    if (!$_SESSION["logged_in"]) redirect("MainController","refresh");
    if (!isset($_SESSION["logged_in"])) redirect("MainController", "refresh");
  }
?>

<style>


.neon {
  position:fixed;bottom:10px;right:10px;
  text-align: center;
  font-size: 20px;
  /*margin: 20px 0 20px 0;*/
  text-decoration: none;
  -webkit-transition: all 0.5s;
  -moz-transition: all 0.5s;
  transition: all 0.5s;
}

.neon {
  color: #FFDD1B;
  font-family: Pacifico;
}

.neon:hover {
  -webkit-animation: neon3 1.5s ease-in-out infinite alternate;
  -moz-animation: neon3 1.5s ease-in-out infinite alternate;
  animation: neon3 1.5s ease-in-out infinite alternate;
}

.neon:hover {
  color: #ffffff;
}
/*glow for webkit*/

@-webkit-keyframes neon3 {
  from {
    text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #fff, 0 0 40px #FFDD1B, 0 0 70px #FFDD1B, 0 0 80px #FFDD1B, 0 0 100px #FFDD1B, 0 0 150px #FFDD1B;
  }
  to {
    text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #fff, 0 0 20px #FFDD1B, 0 0 35px #FFDD1B, 0 0 40px #FFDD1B, 0 0 50px #FFDD1B, 0 0 75px #FFDD1B;
  }
}

/*glow for mozilla*/
@-moz-keyframes neon3 {
  from {
    text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #fff, 0 0 40px #FFDD1B, 0 0 70px #FFDD1B, 0 0 80px #FFDD1B, 0 0 100px #FFDD1B, 0 0 150px #FFDD1B;
  }
  to {
    text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #fff, 0 0 20px #FFDD1B, 0 0 35px #FFDD1B, 0 0 40px #FFDD1B, 0 0 50px #FFDD1B, 0 0 75px #FFDD1B;
  }
}
/*glow*/

@keyframes neon3 {
  from {
    text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #fff, 0 0 40px #FFDD1B, 0 0 70px #FFDD1B, 0 0 80px #FFDD1B, 0 0 100px #FFDD1B, 0 0 150px #FFDD1B;
  }
  to {
    text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #fff, 0 0 20px #FFDD1B, 0 0 35px #FFDD1B, 0 0 40px #FFDD1B, 0 0 50px #FFDD1B, 0 0 75px #FFDD1B;
  }
}
/*REEEEEEEEEEESPONSIVE*/

@media (max-width: 650px) {
  #container {
    width: 100%;
  }
  p {
    font-size: 3.5em;
  }
}
</style>

<body>
  <script>
    var site_url = '<?php echo base_url(); ?>';
  </script>
  <div id="header-container">
    <div class="background-header">
    </div>
  </div>
  <div id="navigation-container">
    <div class="navigation hideMin">
      <?php echo $navigation; ?>
    </div>
  </div>
  <div id="content-container">
    <div class="header">
      <?php echo $header; ?>
    </div>    
    <div class="content">
      <?php echo $content; ?>
    </div>
    <div class="neon hideOnMobile">bhaktiidolatama</div>
  </div>
  <div class="clearfix"></div>
  <div id="footer-container">
    <div class="footer">
      <?php include("ConfigInfo.php"); ?>
      <?php echo $footer; ?>
    </div>
  </div>
  <div class="loading" style="display:none;">
    <div class="overlay"></div>
    <div class="loadingItem">
      <img src="<?php echo base_url(); ?>images/ajax-loader.gif"/>
    </div>
  </div>
</body>
</html>