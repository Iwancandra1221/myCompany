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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
  <link rel="stylesheet" href="<?php echo base_url()?>css/style-general.css">
  <link rel="stylesheet" href="<?php echo base_url()?>css/style-dashboard.css">
  <link rel="stylesheet" href="<?php echo base_url()?>css/style-navigation.css">
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

  <!-- Bootstrap -->
  <link href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
  <script src="<?php echo base_url('assets/bootstrap/js/bootstrap.min.js');?>"></script>
  <link href="<?php echo base_url('assets/bootstrap/css/datatables.bootstrap.min.css');?>" rel="stylesheet"><!-- * -->
  <script src="<?php echo base_url('assets/bootstrap/js/datatables.bootstrap.min.js');?>"></script>
  <script src="<?php echo base_url('assets/bootstrap/js/datatables.min.js');?>"></script>


  <script>
    $(document).ready(function(){
        $(".unavailable").click(function(){
          alert("opsi dashboard ini belum tersedia");
        });
    });
  </script>
  <style>
    .leftMenu {
      color:white;
      width:160px;
      margin:10px auto 10px auto !important;
      line-height: 30px;
      vertical-align: middle;
      padding:5px;
      border:1px solid white;
      border-radius:10px;
      text-align: center;
      font-size: 12px;
      cursor: pointer;
      background-color: rgb(0, 0, 0, 0.5);
    }
    .leftMenu:hover {
      background-color: yellow;
      color: black;
    }
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
    <div class="background-header"></div>
    <div class="header"><?php echo $header; ?></div>
    <div class="footer"><?php echo $footer; ?></div>
    <div class="navigation hideMin"><?php echo $navigation; ?></div>
  </div>
  <div id="content_container">
    <div class="leftwrapper">
      <div class="content">
        <div class="leftMenu">KARYAWAN</div>
        <div class="leftMenu unavailable">MENU 2</div>
        <div class="leftMenu unavailable">MENU 3</div>
        <div class="leftMenu unavailable">MENU 4</div>
        <div class="leftMenu unavailable">MENU 5</div>
        <div class="leftMenu unavailable">MENU 6</div>
      </div>
    </div>
    <div class="rightwrapper">
      <div class="dashboard-content"><?php echo $content; ?></div>
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="loading">
    <div class="overlay"></div>
    <div class="loadingItem"><img src="<?php echo base_url(); ?>images/ajax-loader.gif"/></div>
  </div>  
</body>
</html>