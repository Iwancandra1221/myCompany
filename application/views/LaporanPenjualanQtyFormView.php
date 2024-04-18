    <!-- css for loading -->
    <style type="text/css">
      .disablingDiv{
        z-index:1;
         
        /* make it cover the whole screen */
        position: fixed; 
        top: 0%; 
        left: 0%; 
        width: 100%; 
        height: 100%; 
        overflow: hidden;
        margin:0;
        /* make it white but fully transparent */
        background-color: white; 
        opacity:0.5;  
      }
      .loader {
          position: absolute;
          left: 50%;
          top: 50%;
          z-index: 1;
          /*width: 150px;
          height: 150px;*/
          margin: -75px 0 0 -75px;
          border: 16px solid #f3f3f3;
          border-radius: 50%;
          border-top: 16px solid #3498db;
          width: 120px;
          height: 120px;
          -webkit-animation: spin 2s linear infinite;
          animation: spin 2s linear infinite;
      }

      @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
      }
    </style>
    <!--  -->

    <!-- flash message -->
    <div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
      <div style="padding: 5px;">
      <?php
          if (isset($_GET['insertsuccess'])) {
            echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Inserted <strong>Successfully !</strong></div>";
          }

          if (isset($_GET['updatesuccess'])) {
            echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Updated <strong>Successfully !</strong></div>";
          }

          if (isset($_GET['deletesuccess'])) {
            echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Removed <strong>Successfully !</strong></div>";
          }
      ?>

      <?php
          if (isset($_GET['inserterror'])) {
            echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Insert Data Error ! ".$_GET['inserterror']."</strong></div>";
          }

          if (isset($_GET['updateerror'])) {
            echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Update Data Error ! ".$_GET['updateerror']."</strong></div>";
          }

          if (isset($_GET['deleteerror'])) {
            echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Delete Data Error !</strong></div>";
          }

          if (isset($_GET['generateerror'])) {
            echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>Generate Data Error !".$_GET['generateerror']."</strong></div>";
          }
      ?>
      </div>
    </div>

    <div class="container">
      <?php echo form_open('LaporanPenjualanQty/Preview'); ?>    
      <!-- <form action="LaporanPenjualanQtyCtr/Preview" method="post" target="_blank"> -->
        <div class="form-group">
          <label>Dari Bulan</label>
          <div class="input-group date">
            <input type="text" class="form-control" id="dp" placeholder="yyyy-mm" name="monStart" required>
            <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Sampai Bulan</label>
          <div class="input-group date">
            <input type="text" class="form-control" id="dp2" placeholder="yyyy-mm" name="monEnd" required>
            <div class="input-group-addon">
              <span class="glyphicon glyphicon-th"></span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Produk</label><br>
           <input type="radio" name="radLokalImport" id="radLokal" value="LOKAL"> <label for="radLokal">Lokal</label>
          &nbsp;&nbsp;&nbsp;
          <input type="radio" name="radLokalImport" id="radImport" value="IMPORT"> <label for="radImport">Import</label>
          &nbsp;&nbsp;&nbsp;
          <input type="radio" name="radLokalImport" id="radLokImp" value="LOKIMP"> <label for="radLokImp">Gabung</label>
        </div>
        <div class="form-group">
          <label>Divisi</label>
          <select id="selDivisi" name="selDivisi" class="form-control">
            <!-- <option value='ALL' selected>--ALL DIVISI--</option> -->
            <?php
              for($i=0;$i<count($divisi);$i++){
                echo "<option value='".$divisi[$i]->Divisi."'>".$divisi[$i]->Divisi."</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Jenis Transaksi</label><br>
          <!-- <input type="radio" name="radJenisTrans" id="radJ" value="J"> <label for="radNotSent">Jual</label>
          &nbsp;&nbsp;&nbsp;
          <input type="radio" name="radJenisTrans" id="radR" value="R"> <label for="radSent">Retur</label>
          &nbsp;&nbsp;&nbsp; -->
          <input type="radio" name="radJenisTrans" id="radN" value="Nett"> <label for="radAllStat">Jual - Retur (Netto)</label>
        </div>
        <!-- <input type="submit" class="btn btn-primary" value="Process Data" id="cmdProcessData" name="submitProcess"> -->
        <!-- <input type="submit" class="btn btn-primary" value="Process Data 3 Bulan Terakhir" id="cmdProcessData3Bln" name="submitProcess3Bln"> -->
        <input type="submit" class="btn btn-primary" value="Preview Data" id="cmdPreviewData" name="cmdPreviewData">
        <input type="submit" class="btn btn-warning" value="Preview Data Compare Tahun Lalu" id="cmdPreviewDataCompare" name="cmdPreviewDataCompare">
        <!-- <input type="button" class="btn btn-danger" onclick="location.href = '../masterDbCtr';" value="Cancel"> -->
      <?php echo form_close(); ?>

      <!--
      <label>No Faktur</label>
      <table id="tblFaktur" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <?php 
          echo "<thead>";
          echo "<tr>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";

          echo "</tbody>"; ?>
      </table>
      -->
    </div> <!-- /container -->

  <script>
    $(document).ready(function() {
      $('#dp').datepicker({
        format: "yyyy-mm",
        autoclose: true,
        viewMode: "months", 
        minViewMode: "months"
      });

      $('#dp2').datepicker({
        format: "yyyy-mm",
        autoclose: true,
        viewMode: "months", 
        minViewMode: "months"
      });

      $('#example').DataTable({
        "pageLength": 25
      });
      $("#flash-msg").delay(1200).fadeOut("slow");

      $("#txtTujuan").on('click', function(e){
        popupWindow('picktujuan');
      });

      $("#chkAllTujuan").attr("checked",true);
      $("#txtTujuan").hide();

      $("#radLokImp").attr("checked",true);
      $("#radN").attr("checked",true);

      $("#loading").hide();
      $("#disablingDiv").hide();

    } );

    $(document).ajaxStart(function() {
      $("#disablingDiv").show();
      $("#loading").show();
    });

    $(document).ajaxStop(function() {
      $("#loading").hide();
      $("#disablingDiv").hide();
    });
  </script>
