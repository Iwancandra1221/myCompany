<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
  var dealers = <?php echo(json_encode($Dealers));?>;

    $(document).ready(function() {
      //$("#LogOmzetNasional").hide();
      var err = "<?php echo($err); ?>";
      if (err!="") {
        alert(err);
      }
      //CheckData();
      $("#Dealer").autocomplete({
        source: dealers
      });


      $("#Tahun").change(function() {
        //CheckData();
      });

      $('#Bulan').on('change', function() {
        //CheckData();
      });

      $('#Dealer').on('change', function() {
        var Dealer = $("#Dealer").val();
        var dArray = Dealer.split(" - ");
        $("#KodeDealer").val(dArray[2]);
      });

    } );
</script>

<style type="text/css">
  th, td { border:1px solid #000; padding: 2px 10px 2px 10px; }
</style>

<div class="container">
  <div class="page-title"><?php echo($opt);?></div>
  <?php 
    echo form_open($formURL, array("target"=>"_blank")) 
  ?>
  <div class="form-container">
    <div class="row TANGGAL">
      <div class="col-3 col-m-4" align="right">Tanggal Awal</div>
      <div class="col-8 col-m-6 date">
        <input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1">
      </div>
      <div class="col-1 col-m-2">
        <div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
      </div>
    </div>

    <div class="row TANGGAL">
      <div class="col-3 col-m-4" align="right">Tanggal Akhir</div>
      <div class="col-8 col-m-6 date">
        <input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2">
      </div>
      <div class="col-1 col-m-2">
        <div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-3 col-m-4" align="right">Pilihan Laporan</div>
      <div class="col-9 col-m-8">
        <select id="OptReport" name="OptReport">
        <?php
          echo("<option value='001'>001.Laporan Per Dealer Per No Faktur</option>");
        ?>
        </select>
      </div>
    </div>    
    <div class="row">
      <div class="col-3 col-m-4" align="right">Nama Dealer</div>
      <div class="col-9 col-m-8">
        <?php
          $attr = array 
          (
            'placeholder' => 'Dealer',
            'id' => 'Dealer',
            'style'=>'width:90%;'
          );
          echo BuildInput('text','Dealer',$attr); 
        ?>
      </div>
    </div>
    <div class="row">
      <div class="col-3 col-m-4" align="right"></div>
      <div class="col-9 col-m-8">
        <?php
          $attr = array 
          (
            'placeholder' => 'Kode Dealer',
            'id' => 'KodeDealer',
            'style'=>'width:90%;'
          );
          echo BuildInput('text','KodeDealer',$attr); 
        ?>
      </div>
    </div>
    <div class="row" align="center">
      <div class="col-12 col-m-12">
        <input type = "submit" name="btnPreview" value="PREVIEW"/>
        <?php if ($btnPDF==1) { ?>
        <input type = "submit" name="btnPdf" value="PREVIEW PDF"/>
        <?php } ?>
        <?php if ($btnExcel==1) { ?>
        <input type = "submit" name="btnExcel" value="EXCEL"/>
        <?php } ?>
      </div>
    </div>
  </div>
  <?php echo form_close(); ?>
  <div style='clear:both;height:20px;'></div>

</div> 