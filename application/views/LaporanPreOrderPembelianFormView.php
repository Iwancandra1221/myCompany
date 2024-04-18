<style type="text/css">
  .row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
  }
  .row-label, .row-input {
    float:left;
  }
  .row-label {
    padding-left: 15px;
    width:180px;
  }
  .row-input {
    width:420px;
  }

  input {
    color:#000;
  }
</style>
<div class="container">
  <div class="page-title"><?php echo(strtoupper($title));?></div>
      <?php 
        echo form_open("LaporanPreOrderPembelian/Preview", array("target"=>"_blank"));
      ?>
      <div class="form-container">
        <div class="row">
           <div class="row-label">
              Divisi
           </div>
           <div class="row-input">
              <select name='divisi'>
                <?php 
                for($i=0;$i<count($divisi);$i++)
                {
                  echo("<option value='".$divisi[$i]."'>".$divisi[$i]."</option>");
                }
                ?>
              </select>
           </div>
        </div>
        <div class="row">
           <div class="row-label">
              Bulan	**
           </div>
           <div class="row-input">
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
              Tahun **
           </div>
           <div class="row-input">
              <input type = "number" name = "yyyy" value = "<?php echo(date('Y'));?>" />
           </div>
        </div>
        <div class="row">
           <div class="row-label">
              Periode **
           </div>
           <div class="row-input">
              <select id="pp" name="pp">
                <option value="1">1</option>
                <option value="2">2</option>
              </select>
           </div>
        </div>
        <div class="row" style="display:none;">
           <div class="row-label">
              Cabang (CBG)
           </div>
           <div class="row-input">
              <select name="cbg">
                  <option value="ALL">ALL</option>
        		<?php 

        		?>
              </select>
           </div>
        </div>
        <div class="row">
           <div class="row-label">
              Pilihan Report
           </div>
           <div class="row-input">
              <select name="opsi">
                <?php if ($menu=="ALL") { ?>
                <option value="A01">A01. Laporan Forecast Beli/Jual/Stock Per Periode All Kota</option>
                <option value="A02">A02. Laporan Forecast Beli/Jual/Stock Per Periode All Cabang</option>
                <option value="B01">B01. Laporan Forecast Jual All Cabang Gabungan</option>
                <option value="B02">B02. Laporan Forecast Beli All Cabang Gabungan</option>
                <option value="B03">B03. Laporan Forecast Jual All Cabang</option>
                <option value="B04">B04. Laporan Forecast Beli All Cabang</option>
                <option value="C01">C01. Laporan Forecast 8 Periode Gabungan Semua Kota</option>
                <option value="C02">C02. Laporan Forecast 8 Periode Per Kota</option>
                <option value="C03">C03. Laporan Forecast 8 Periode Per Kota Per GroupGudang</option>
                <?php } else { ?>
                <option value="C01">C01. Laporan Forecast 8 Periode Gabungan Semua Kota</option>
                <?php } ?>
              </select>
           </div>
        </div>
        <div class="row">
           <div class="row-label">Opsi</div>
           <div class="row-input">
              <input type="checkbox" id="cbox1" name="cbox1" value="Y" checked="checked">
              <label for="cbox1">Sembunyikan Barang yang Total Ordernya 0</label>
           </div>
        </div>
        <div class="row" style="display:none;">
           <div class="row-label">
              Email
           </div>
           <div class="row-input">
              <select name="email">
              	<option value="Y">Tampilkan dan Email</option>
              	<option value="N">Tampilkan Saja</option>
              </select>
           </div>
        </div>
        <div class="row" align="center" style="padding-top:50px;">
           <input type = "submit" name="btnPreview" value="PREVIEW"/>
           <input type = "submit" name="btnExcel" value="EXCEL"/>
           <!--
           <a href="<?php echo base_url("index.php/LaporanPreOrderPembelian/Export_Excel")?>" target="_blank"><input type = "button" value="EXCEL"/></a>
            -->
        </div>
     </div>
     <?php echo form_close(); ?>
</div> <!-- /container -->
