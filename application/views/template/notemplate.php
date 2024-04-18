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

    <link rel="stylesheet" href="<?php echo base_url()?>css/style.css">
    <link rel="stylesheet" href="<?php echo base_url()?>css/font-awesome.min.css">

    <link rel="stylesheet" href="<?php echo base_url()?>js/vendor/datatable/css/jquery.dataTables.css">
    <link href='<?php echo base_url(); ?>gfont/gfont.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo base_url(); ?>css/datetimepicker.css' rel='stylesheet' type='text/css'>

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
 
</head>

<body>
  <script>
    var site_url = '<?php echo site_url(); ?>';
  </script>
    <?php echo $content; ?>
</body>
</html>