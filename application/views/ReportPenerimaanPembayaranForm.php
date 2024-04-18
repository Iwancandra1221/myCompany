<script>

    function LoadMasterDealer() {
      var selectedWilayah = $('#wilayah').val();  
   
      if ($.fn.DataTable.isDataTable('#dealerTable')) {
          $('#dealerTable').DataTable().destroy();
      }

      $('#dealerTable').dataTable( {
          "bProcessing": true,
          "bServerSide": true,
          "sAjaxSource": "<?php echo site_url('ReportPenerimaanPembayaran/LoadMasterDealer'); ?>?wilayah=" + selectedWilayah,
          "oLanguage": {
              "sLengthMenu": "Menampilkan _MENU_ Data per halaman",
              "sZeroRecords": "Maaf, Data tidak ada",
              "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
              "sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
              "sSearch": "",
              "sInfoFiltered": "",
              "oPaginate": {
                  "sPrevious": "Sebelumnya",
                  "sNext": "Berikutnya"
              }
          }, 
      });
      $('#popupForm').show();
    }
 

    function finddealer(){
          var dealer = $('#dealer').val();
          var selectedWilayah = $('#wilayah').val();  
          $(".loading").show();
          $.ajax({
              type: "POST",
              url : '<?php echo site_url("ReportPenerimaanPembayaran/findDealer"); ?>', 
              data: { dealer: dealer, wilayah: selectedWilayah },
              dataType: "json",
              success: function(response) {  
                  if (response.success) {  
                      $('#outputLabel').text(response.result); 
                  } else {
                      alert("Kode Dealer Not Found");
                      $('#outputLabel').text(""); 
                      $('#dealer').val(""); 
                  }
                  $(".loading").hide();
              },
              error: function() {
                  console.error("Error dalam AJAX request.");
                  $(".loading").hide();
              }
          }); 
    }

    $(document).ready(function() { 
     $('#closeButton').click(function() {
        $('#popupForm').hide();
    });
      $("#wilayah").change(function() {
         $('#outputLabel').text(""); 
         $('#dealer').val(""); 
      });

      $('#searchDealer').click(function(){ 
          finddealer();
      });

      $('#browseDealer').click(function(){
        LoadMasterDealer();
      });
  
      $('#dealerTable tbody').on('click', 'tr', function() {
        var data = $('#dealerTable').DataTable().row(this).data();
 
        if (data) {
            var dealerCode = data[0]; 
            var dealerName = data[1]; 

            $('#outputLabel').text(dealerName);
            $('#dealer').val(dealerCode); 
        }

        $('#popupForm').hide();
      });

      $('#dp1').datepicker({
        format: "mm/dd/yyyy",
        autoclose: true
      });

      $('#dp2').datepicker({
        format: "mm/dd/yyyy",
        autoclose: true
      });
    } );
</script>

<style type="text/css">
  #closeButton {
      position: absolute;
      bottom: 20px;
      right: 20px;
      text-align: right;
  }
  #popupForm {
        width: 800px;
        height: 600px;
        overflow: auto;
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #f2f2f2;
        padding: 20px;
        border: 1px solid #ccc;
        z-index: 9999;
    }
</style>

<div class="container">
  <div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open("ReportPenerimaanPembayaran/Proses", array("target"=>"_blank")) ?>
    <div class="form-container">
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
         <div class="col-3 col-m-4">Wilayah</div>
         <div class="col-9 col-m-8">
            <select name='wilayah' id="wilayah">  
              <?php 
              for($i=0;$i<count($wilayah);$i++)
              {
                echo("<option value='".$wilayah[$i]["WILAYAH"]."'>".$wilayah[$i]["WILAYAH"]."</option>");
              }
              ?>
            </select>
         </div>
      </div>
      <div class="row">
         <div class="col-3 col-m-4">Dealer</div>
         <div class="col-9 col-m-8"> 
            <input type="text" id="dealer" name='dealer' placeholder="Kode Dealer">
            <input id="searchDealer" type="button" value ="Search"></input> 
            <input id="browseDealer" type="button" value ="Browse Dealer"></input>  
            <br>
         </div>
      </div>

      <div class="row">
         <div class="col-3 col-m-4"></div>
         <div class="col-9 col-m-8"> 
            <label for="dealer" id="outputLabel"></label>
         </div>
      </div>
      <div class="row">
         <div class="col-3 col-m-4">
            Tipe Pembayaran
         </div>
         <div class="col-9 col-m-8">
            <select name="opsi">
              <option value="P01">Semua</option>
              <option value="P02">Cash</option>
              <option value="P03">Check</option>
              <option value="P04">Giro</option>
              <option value="P05">Transfer</option>
              <option value="P06">Virtual Account</option>
            </select>
         </div>
      </div>
      <div class="row" style="display:none;">
         <div class="col-3 col-m-4">
            Email
         </div>
         <div class="col-9 col-m-8">
            <select name="email">
            	<option value="Y">Tampilkan dan Email</option>
            	<option value="N">Tampilkan Saja</option>
            </select>
         </div>
      </div>
      <div class="row" align="center" style="padding-top:50px;">
         <input type = "submit" name="btnPreview" value="PREVIEW"/>
         <input type = "submit" name="btnExcel" value="EXCEL"/>
      </div>
    </div>
  <?php echo form_close(); ?>
</div> 

<div id="popupForm"> 
    <div >
        <table id="dealerTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th> 
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
    </div>
    <button id="closeButton">Close</button>
</div>
