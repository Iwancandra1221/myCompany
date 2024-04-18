<style type="text/css">
      body { }

      .form-container {
        width: 400px;
        height: 250px;
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#d2ff52+0,91e842+100;Neon */
        background: #d2ff52; /* Old browsers */
        background: -moz-linear-gradient(top, #d2ff52 0%, #91e842 100%); /* FF3.6-15 */
        background: -webkit-linear-gradient(top, #d2ff52 0%,#91e842 100%); /* Chrome10-25,Safari5.1-6 */
        background: linear-gradient(to bottom, #d2ff52 0%,#91e842 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#d2ff52', endColorstr='#91e842',GradientType=0 ); /* IE6-9 */
        border:1px solid blue;
        border-radius:15px;
        padding:15px;
      }

      .row {
        line-height:30px; 
        vertical-align:middle;
        clear:both;
      }
      .row-label, .row-input {
        float:left;
      }
      .row-label {
        width:40%;
      }
      .row-input {
        width:60%;
      }
</style>
<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('main','refresh');
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include ('template/commonhead.php'); ?>
  </head>

  <body>
    <!-- Fixed navbar -->
    <?php include ('template/menubar.php'); ?>

    <div class="container">
      <div class="page-title"><?php echo(strtoupper($title));?></div>
      <?php echo form_open(base_url("LaporanEkspedisiBhakti/Preview"), array("target"=>"_blank")); ?>
      <!-- <form action = "laporan_ekspedisi_pabrik.php" method = "GET"> -->
         <div class="form-container">
            <div class="row">
               <div class="row-label">
                  Tanggal (DD)
               </div>
               <div class="row-input">
                  <input type = "number" name = "dd" value = "<?php echo(date('d'));?>"/>
               </div>
            </div>
            <div class="row">
               <div class="row-label">
                  Bulan  (MM)  
               </div>
               <div class="row-input">
                  <!-- <input type = "number" name = "mm" /> -->
                  <select id="mm" name="mm">
                  <?php 
                    foreach($months as $m)
                    {
                      echo("<option value='".$m->month."'".(($m->month==date('m'))?" selected":"").">".$m->month_name."</option>");
                    }
                  ?>
                </select>
               </div>
            </div>
            <div class="row">
               <div class="row-label">
                  Tahun (YYYY)
               </div>
               <div class="row-input">
                  <input type = "number" name = "yyyy" value = "<?php echo(date('Y'));?>"/>
               </div>
            </div>
            <div class="row">
               <div class="row-label">
                  CABANG (CBG)
               </div>
               <div class="row-input">
                  <select name="cbg">
                <?php 
                  foreach ($branches as $b)
                  {
                      echo ('<option value="'.$b->branch_name.'">'.$b->branch_name.'</option>');
                  }
                ?>
                  </select>
               </div>
            </div>
            <div class="row">
               <div class="row-label">
                  Email
               </div>
               <div class="row-input">
                  <select name="email">
                    <option value="N">Tampilkan Saja</option>
                    <option value="Y">Tampilkan dan Email</option>
                  </select>
               </div>
            </div>
            <div class="row" align="center" style="padding-top:20px;">
               <input type = "submit" name="btnPreview" value="PROSES LAPORAN EKSPEDISI BHAKTI" value="PROSES"/>
               <input type = "submit" name="btnExcel" value="EXCEL"/>
            </div>
         </div>
      <!-- </form> -->
      <?php echo form_close(); ?>
    </div> <!-- /container -->
  </body>
</html>