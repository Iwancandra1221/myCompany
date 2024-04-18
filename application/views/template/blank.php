<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo $title; ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=1024">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="stylesheet" href="<?php echo base_url()?>css/webHrd.css">
    <link rel="stylesheet" href="<?php echo base_url()?>css/style.css">
    <link rel="stylesheet" href="<?php echo base_url()?>css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url()?>js/vendor/datatable/css/jquery.dataTables.css">
    <link href='<?php echo base_url(); ?>gfont/gfont.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo base_url(); ?>css/datetimepicker.css' rel='stylesheet' type='text/css'>

    <!-- <link href='http://fonts.googleapis.com/css?family=Istok+Web:400,700,400italic,700italic|Oxygen:400,300,700' rel='stylesheet' type='text/css'> -->
    <script src="https://use.fontawesome.com/962900c914.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Libre+Franklin" rel="stylesheet">

    <script src="<?php echo base_url()?>js/vendor/jquery-1.10.2.min.js"></script>
    <script src="<?php echo base_url()?>js/vendor/datatable/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url()?>js/vendor/jquery.datetimepicker.js"></script>
    <script src="<?php echo base_url()?>js/vendor/phpjs.js"></script>
    <script src="<?php echo base_url()?>js/lib.js"></script>
    <script src="<?php echo base_url()?>js/main.js"></script>
    <link rel="icon" href="<?php echo base_url();?>images/icon.png" type="image/x-icon">

    <!-- <link href="<?php echo base_url()?>assets/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- <link href="<?php echo base_url()?>assets/css/stylish-portfolio.css" rel="stylesheet"> -->
    <style>
    </style>
</head>

<?php
  $this->load->model('ModuleModel');
  $CI =& get_instance();
?>

<body>
  <script>
    var site_url = '<?php echo site_url(); ?>';
  </script>
  <div id="header-container">
    <div class="background-header">
    </div>
    <div class="header">
      <?php echo $header; ?>
    </div>
    <div class="footer">
      <?php echo $footer; ?>
    </div>
  </div>
  <div id="content_container">
    <div class="navigation hideMin">
      <?php echo $navigation; ?>
    </div>
    <div class="contentwrapper">
      <div class="content">
        <?php echo $content; ?>
      </div>
    </div>
    <div class="clearfix"></div>
  </div>
  <div class="loading">
    <div class="overlay"></div>
    <div class="loadingItem">
      <img src="<?php echo base_url(); ?>images/ajax-loader.gif"/>
    </div>
  </div>
  
  </div>
</body>
</html>