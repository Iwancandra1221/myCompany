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
      ?>
      </div>
    </div>


    <div class="container">
    <?php echo form_open("PabrikPengiriman/UpdateRequest");?>
    <!-- <form action="PabrikPengirimanCtr/UpdateRequest" method="post"> -->
      <div class="form-group">
        <label>Tanggal Kirim</label>
        <div class="input-group date">
          <input type="text" class="form-control" id="dp" placeholder="mm/dd/yyyy" name="dateTglKirim" required>
          <div class="input-group-addon">
          <span class="glyphicon glyphicon-th"></span>
        </div>
      </div>
      </div>
      <div class="form-group">
        <label>Tujuan</label>
        <input type="text" class="form-control" name="txtTujuan" id="txtTujuan" placeholder="PT Cabang Tujuan" required readonly>
        <small id="NamaCust" class="form-text text-muted"></small>
        <small id="AlmCust" class="form-text text-muted"></small>
      </div>
      <div class="form-group">
        <label>Nama Ekspedisi</label>
        <input type="text" class="form-control" name="txtNamaEks" id="txtNamaEks" placeholder="Input Nama Ekspedisi" required>
      </div>
      <div class="form-group">
        <label>Nomor Ekspedisi</label>
        <input type="text" class="form-control" name="txtNoEks" id="txtNoEks" placeholder="Input Nomor Ekspedisi" required>
      </div>
      <div class="form-group">
        <label>Nomor Container</label>
        <input type="text" class="form-control" name="txtNoContainer" id="txtNoContainer" placeholder="Input Nomor Container" required>
      </div>
      <div class="form-group">
        <label>Container Seal</label>
        <input type="text" class="form-control" name="txtContainerSeal" id="txtContainerSeal" placeholder="Input Container Seal" required>
      </div>
      <div class="form-group">
        <label>Nomor Plat Mobil</label>
        <input type="text" class="form-control" name="txtPlatMobil" id="txtPlatMobil" placeholder="Input Nomor Plat Mobil" required>
      </div>
      <div class="form-group">
        <label>Nama Supir</label>
        <input type="text" class="form-control" name="txtNamaSupir" id="txtNamaSupir" placeholder="Input Nama Supir" required>
      </div>
      <div class="form-group">
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
      </div>
      <input type="hidden" name="hdnJumlahFaktur" id="hdnJumlahFaktur" value="0">
      <input type="submit" class="btn btn-primary" value="Submit">
      <!-- <input type="button" class="btn btn-danger" onclick="location.href = '../masterDbCtr';" value="Cancel"> -->
    <?php echo form_close();?> 

    </div> <!-- /container -->


  <script>
    $(document).ready(function() {
      $('#dp').datepicker({
        format: "mm/dd/yyyy",
        autoclose: true
      });

      $('#example').DataTable({
        "pageLength": 25
      });
      $("#flash-msg").delay(1200).fadeOut("slow");

      $("#txtTujuan").on('click', function(e){
        popupWindow('picktujuan');
      });
    } );


    function popupWindow(id){
       window.open('PabrikPengiriman/PickTujuan?id=' + encodeURIComponent(id),'popuppage',
      'width=800,toolbar=1,resizable=1,scrollbars=yes,height=600,top=0,left=100');
    }
    
    function updateValue(id, kd_plg, nm_plg, alm_plg)
    {
        // this gets called from the popup window and updates the field with a new value
        document.getElementById("txtTujuan").value = kd_plg;
        document.getElementById("NamaCust").innerHTML = nm_plg.split('_').join(' ');
        document.getElementById("AlmCust").innerHTML = "("+alm_plg.split('_').join(' ')+")";

        loadFaktur(kd_plg);
    }

    function loadFaktur(kodetujuan){
      $.ajax({
        url : '<?php echo(site_url("PabrikListUpdate/GetListFaktur")); ?>',
        type: "POST",
        data: {kodetujuan : kodetujuan},
        dataType: "JSON",
        success: function(data)
        {
          $("#tblFaktur tbody tr").remove();
          for(var i=0;i<data.length;i++)
          {
              var tr="<tr>";
              var td0="<td width='5%'><input type='checkbox' name='chkNoFaktur_"+i+"' id='chkNoFaktur_"+i+"'></td>";
              var td1="<td>"+data[i]["No_Faktur"]+"</td><input type='hidden' name='noFaktur_"+i+"' id='noFaktur_"+i+"' value='"+data[i]["No_Faktur"]+"'>";             
              
              $("#tblFaktur tbody").append(tr+td0+td1);
          }
          $('#hdnJumlahFaktur').val(data.length);
          $('#tblFaktur').DataTable();
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
          alert('Error get data from ajax');
        }
      });
    }

  </script>
