<style type="text/css">
   .row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
   }
   .row-label, .row-input {
    float:left;
   }
   .row-input {
    width:420px;
   }
</style>

<script>
    $(document).ready(function() {
      $('#dp1').datepicker({
         format: "yyyy-mm-dd",
         autoclose: true
      }).on('changeDate', function(e) { //changeDate
         var StartDt = $('#dp1').datepicker('getDate');
         $('#dp2').datepicker("setStartDate", StartDt);
      });
      
      $('#dp2').datepicker({
         format: "yyyy-mm-dd",
         autoclose: true
      }).on('changeDate', function(e) { //changeDate
         var EndDt = $('#dp2').datepicker('getDate');
         $('#dp1').datepicker("setEndDate", EndDt);
      });
   } );
</script>

<div class="container">
   <div class="page-title"><?php echo(strtoupper($title));?></div>
   <?php 
        echo form_open("MutasiHarian/Proses", array("target"=>"_blank"));
   ?>
   <div class="form-container">
      
      <div class="row">
         <div class="col-3 col-m-3 row-label">Tanggal Transaksi</div>
         <div class="col-3 col-m-3 date">
            <input type="text" class="form-control" id="dp1" placeholder="yyyy-mm-dd" name="awal" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" required>
         </div>
         <div class="col-1 col-m-1 text-center">SD</div>
         <div class="col-3 col-m-3 date">
            <input type="text" class="form-control" id="dp2" placeholder="yyyy-mm-dd" name="akhir" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" required>
         </div>
      </div>
            
      <div class="row">
         <div class="col-3 col-m-3 row-label">Kode Transaksi</div>
         <div class="row-input">
               <select name="kode_transaksi" class="form-control">
                  <option value="K">K</option>
                  <option value="M">M</option>
                  <option value="T" selected>T</option>
               </select>
         </div>
      </div>

      <div class="row">
         <div class="col-3 col-m-3 row-label">Type Cetak</div>
         <div class="row-input">
            <select name="type_cetak" class="form-control">
               <option value="FR">FR</option>
               <option value="MI">MI</option>
               <option value="PG">PG</option>
               <option value="RP">RP</option>
               <option value="SJ" selected>SJ</option>
            </select>
         </div>
      </div>

      <div class="row">
         <div class="col-3 col-m-3 row-label">Kategori Barang</div>
         <div class="row-input">
            <select name="kategori_barang" class="form-control">
               <option value="P">PRODUK</option>
               <option value="S">SPAREPART</option>
            </select>
         </div>
      </div>

      <div class="row">
         <div class="col-3 col-m-3 row-label">Gudang</div>
         <div class="row-input">
            <select name="gudang" class="form-control">
               <option value="ALL">ALL</option>
               <?php
                  $jum_gudang = count($gudang->data);
                  if($gudang->result=='sukses'){
                     for ($i=0; $i < $jum_gudang; $i++) { 
               ?>
                        <option value="<?php echo $gudang->data[$i]->Kd_gudang; ?>"><?php echo $gudang->data[$i]->Nm_gudang; ?></option>
               <?php
                     }
                  }
               ?>
            </select>
         </div>
      </div>

      <div class="row" align="center" style="padding-top:50px;">
         <button type="submit" name="btnPDF" class="btn">
            PDF
         </button>
      </div>

   </div>
   <?php echo form_close(); ?>
</div>