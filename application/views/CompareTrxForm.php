<script>
  $(document).ready(function() {
    $('#dp1').datepicker({
      format: "mm/dd/yyyy",
      autoclose: true
    });

    $('#dp2').datepicker({
      format: "mm/dd/yyyy",
      autoclose: true
    });

  });
</script>

<style type="text/css">
</style>

<div class="">
  <?php echo form_open(site_url("CompareTrx/ProsesCompare"), array("target"=>"_blank")); ?>
    <div class="form-container" style="height:500px!important;">
      <div class="row">
        <div class="col-3 col-m-4">Tanggal Awal</div>
        <div class="col-8 col-m-6 date">
          <input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" required>
        </div>
        <div class="col-1 col-m-2">
          <div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Tanggal Akhir</div>
        <div class="col-8 col-m-6 date">
          <input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" required>
        </div>
        <div class="col-1 col-m-2">
          <div class="input-group-addon"><span class="glyphicon glyphicon-th"></span></div>
        </div>
      </div>
      <div class="row">
         <div class="col-3 col-m-4">
            Jenis Transaksi
         </div>
         <div class="col-9 col-m-8">
            <select name='trxType' id="trxType">
              <option value='FAKTUR'>FAKTUR PENJUALAN</option>
              <option value='RETUR'>RETUR PENJUALAN</option>
            </select>
         </div>
      </div>
      <div class="row">
         <div class="col-3 col-m-4">
            Database JKT
         </div>
         <div class="col-9 col-m-8">
            10.1.48.200 - BHAKTI
         </div>
      </div>
      <div class="row">
         <div class="col-3 col-m-4">
            Database Cabang
         </div>
         <div class="col-9 col-m-8">
            <select name='dbCBG' id="dbCBG">
              <?php 
              for($i=0; $i<count($dbCBG); $i++) {
                echo("<option value='".$dbCBG[$i]["namaDB"]."'>".$dbCBG[$i]["cabang"]."</option>");
              }
              ?>
            </select>
         </div>
      </div>
      <div class="row" align="center" style="padding-top:50px;">
         <input type = "submit" name="btnCompare" value="COMPARE"/>
         <!-- <input type = "submit" name="btnExcel" value="EXCEL"/> -->
      </div>
    </div>
  <?php echo form_close(); ?>
</div> 