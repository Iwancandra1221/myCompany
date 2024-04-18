<script>
  $(document).ready(function() {
    $('#TblService').DataTable({
       order: [[0, 'desc']],
      "pageLength": 10
    });
  });
</script>


<div class="container">
  <div class="page-title">
    GRACE PERIOD JT
  </div>

  <div class="row">
    <?php
      if($module=='list'){
    ?>
        <div class="col-6">
          
        </div>
        <div class="col-6 text-right">

          <?php
            if($_SESSION["can_create"]==true){
          ?>
              <a href="<?php echo site_url('Graceperiodjt/add') ?>">
                <button type="button" class="btn-sm btn-primary-dark">
                  ADD
                </button>
              </a>
          <?php
            }
          ?>
        </div>
        <table id="TblService" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="table">
          <thead>
            <tr>
              <td>
                REFF ID
              </td>
              <td>
                Wilayah
              </td>
              <td>
                Divisi
              </td>
              <td>
                Pelanggan
              </td>
              <td>
                Tahun
              </td>
              <td>
                Status
              </td>
              <td>
                Is Cancelled
              </td>
              <td>
                Create Info
              </td>
              <td width="100px" align="center">
                Action
              </td>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach ($list as $key => $l) {
            ?>
                <tr>
                  <td>
                    <?php echo $l->request_no; ?>
                  </td>
                  <td>
                    <?php echo $l->Wilayah; ?>
                  </td>
                  <td>
                    <?php echo $l->Divisi; ?>
                  </td>
                  <td>
                    <?php echo $l->Kd_Plg; ?>
                  </td>
                  <td>
                    <?php echo substr($l->request_no,0,4); ?>
                  </td>
                  <td>
                    <?php echo $l->request_status; ?>
                  </td>
                  <td align="center">
                    <?php 
                      if($l->is_cancelled==1){
                        echo '<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>';
                      }
                    ?>
                  </td>
                  <td>
                    <?php echo $l->User_Name.' - '.date_format(date_create($l->Entry_Time),'d-m-Y'); ?>
                  </td>
                  <td align="center">
                    <a href="<?php echo site_url('Graceperiodjt/view/'.str_replace("=", "", base64_encode($l->request_no))); ?>">
                      <button style="padding:3px 8px" title="view">
                        <i class="glyphicon glyphicon-search" aria-hidden="true"></i>
                      </button>
                    </a>
                    <?php
                      if($_SESSION["can_delete"]==true && $l->is_cancelled==0){
                    ?>
                        <button style="padding:3px 8px" title="delete" onclick="delete_data('<?php echo str_replace("=", "", base64_encode($l->request_no)); ?>');">
                          <i class="glyphicon glyphicon-trash" aria-hidden="true"></i>
                        </button>
                    <?php
                      }
                      if($l->request_status=='APPROVED'){
                    ?>
                        <button style="padding:3px 8px" title="sync" onclick="Sync('<?php echo $l->request_no; ?>');">
                          <i class="glyphicon glyphicon-random" aria-hidden="true"></i>
                        </button>
                    <?php
                      }
                    ?>
                  </td>
                </tr>
            <?php
              }
            ?>
          </tbody>
        </table>
    <?php
      }else if($module=='add' || $module=='view'){
    ?>
          <div class="col-1">
          </div>
          <div class="col-10">

          <?php
            $disabled='';
            if($module=='add'){
          ?>
               <form>
          <?php
            }else{
              $disabled = 'disabled';
            } 

            if($module=='view'){
              $data_number = $detail[0]->request_no;
              $data_wilayah = $detail[0]->Wilayah;
              $data_divisi = $detail[0]->Divisi;
              $data_pelanggan = $detail[0]->Kd_Plg;
              $data_catatan = $detail[0]->request_note;
            }else{
              $data_number = 'AutoNumber';
              $data_wilayah = '';
              $data_divisi = '';
              $data_pelanggan = '';
              $data_catatan = '';
            }

          ?>
            <input id="idr" value="2" type="hidden" />
            <table class="table table-striped" summary="table">
              <thead>
                <tr><th></th></tr>
                <?php
                  if($module=='add'){
                ?>
                    <tr>
                      <td colspan="6" align="right">
                        <button type="submit" name="add" class="btn-sm btn-primary-dark">
                          SAVE
                        </button>
                        <a href="<?php echo site_url('Graceperiodjt') ?>">
                          <button type="button" class="btn-sm btn-dark">
                            CANCEL
                          </button>
                        </a>
                      </td>
                    </tr>
                <?php 
                  }else{
                ?>
                    <tr>
                      <td colspan="6" align="right">
                        <?php
                          if($cekapproved=='show'){
                        ?>
                            <button type="button" class="btn-sm btn-primary-dark" onclick="approved('APPROVED')">
                              APPROVED
                            </button>
                            <button type="button" class="btn-sm btn-danger-dark" onclick="approved('REJECTED')">
                              REJECTED
                            </button>
                           
                        <?php
                          }
                        ?>
                        <a href="<?php echo site_url('Graceperiodjt') ?>">
                          <button type="button" class="btn-sm btn-dark">
                            BACK
                          </button>
                        </a>
                      </td>
                    </tr>
                <?php  
                  }
                ?>
                <tr>
                  <td width="100px">
                    No Request
                  </td>
                  <td colspan="2">
                    <input type="text" class="form-control" name="no_request" value="<?php echo $data_number; ?>" disabled>
                  </td>
                </tr>
                <tr>
                  <td>
                    Wilayah
                  </td>
                  <td colspan="2">
                    <select type="text" class="form-control" id="wilayah" name="wilayah" required <?php echo $disabled; ?> onchange="getpelanggan();" required>
                      <?php
                        foreach ($wilayah as $key => $w) {
                          if($data_wilayah==$w['Nama_Wilayah']){
                            $selected = 'selected';
                          }else{
                            $selected = '';
                          }
                      ?>
                          <option value="<?php echo $w['Kode_Wilayah']; ?>" <?php echo $selected; ?>><?php echo $w['Nama_Wilayah']; ?></option>
                      <?php
                        }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>
                    Divisi
                  </td>
                  <td colspan="2">
                    <select type="text" class="form-control" name="divisi" required <?php echo $disabled; ?> required>
                       <?php
                        foreach ($divisi as $key => $d) {
                          if($data_divisi==$d['Nama_Divisi']){
                            $selected = 'selected';
                          }else{
                            $selected = '';
                          }
                      ?>
                          <option value="<?php echo $d['Nama_Divisi']; ?>" <?php echo $selected; ?>><?php echo $d['Nama_Divisi']; ?></option>
                      <?php
                        }
                      ?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>
                    Pelanggan
                  </td>
                  <td colspan="2">
                    <select type="text" class="form-control" id="pelanggan" name="pelanggan" required <?php echo $disabled; ?> required>
                      <option value="ALL">ALL</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td>
                    Catatan
                  </td>
                  <td colspan="2">
                    <textarea class="form-control" name="catatan" <?php echo $disabled; ?>><?php echo $data_catatan; ?></textarea>
                  </td>
                </tr>
                <tr style="background-color: #eaeaea;">
                  <td align="center">
                    <button type="button" class="btn-sm btn-primary-dark" onclick="addRow()" <?php echo $disabled; ?>>
                      <i class="glyphicon glyphicon-plus" aria-hidden="true"></i>
                    </button>
                  </td>
                  <td>Jatuh Tempo Lama</td>
                  <td>Jatuh Tempo Baru</td>
                </tr>
              </thead>
              <tbody id="tableID">
                <?php
                  if($module!=='add'){
                    $no=1;
                    foreach ($detail as $key => $d) {
                ?>
                      <tr id="srow<?php echo $no; ?>">
                        <td align="center">
                          <button type="button" class="btn-sm btn-danger-dark" onclick="hapusElemen('#srow<?php echo $no; ?>'); return false;" <?php echo $disabled; ?>>
                            <i class="glyphicon glyphicon-minus" aria-hidden="true"></i>
                          </button>
                        </td>
                        <td>
                          <input type="text" name="jtl[]" id="jtl<?php echo $no; ?>" value="<?php echo date_format(date_create($d->JT_Lama),'Y-m-d'); ?>" class="form-control" disabled>
                        <td colspan="3">
                          <input type="text" name="jtb[]" id="jtb<?php echo $no; ?>" value="<?php echo date_format(date_create($d->JT_Baru),'Y-m-d'); ?>" class="form-control" disabled>
                        </td>
                      </tr>
                <?php
                      $no++;
                    }
                  }else{
                ?>
                    <tr id="srow1">
                      <td align="center">
                        <button type="button" class="btn-sm btn-danger-dark" onclick="hapusElemen('#srow1'); return false;" <?php echo $disabled; ?>>
                          <i class="glyphicon glyphicon-minus" aria-hidden="true"></i>
                        </button>
                      </td>
                      <td>
                        <input type="text" name="jtl[]" id="jtl1" class="form-control" required>
                      </td>
                      <td colspan="3">
                        <input type="text" name="jtb[]" id="jtb1" class="form-control" required>
                      </td>
                    </tr>
                <?php
                  }
                ?>
              </tbody>
            </table>
          <?php
            if($module=='add'){
          ?>
              </form>
          <?php
            }
          ?>
        </div>

        <script language="javascript">

           function addRow() {
             var idr = document.getElementById("idr").value;
             var stre;
             var ub="'#srow" + idr + "'";
             var ubc="'merk"+idr+"','jnsbrg"+idr+"'";
             stre = '<tr id="srow' + idr + '"><td align="center"><button type="button" class="btn-sm btn-danger-dark" onclick="hapusElemen('+ub+'); return false;"><i class="glyphicon glyphicon-minus" <?php echo $disabled; ?> aria-hidden="true"></i></button></td><td><input type="text" name="jtl[]" id="jtl'+idr+'" class="form-control" required></td><td colspan="3"><input type="text" name="jtb[]" id="jtb'+idr+'" class="form-control" required></td></tr>';
             $("#tableID").append(stre);
             idr = (idr-1) + 2;
             document.getElementById("idr").value = idr;
             datepicker();
           }

           function hapusElemen(idr) {
             $(idr).remove();
           }

          datepicker();
          function datepicker(){

            var jum = document.getElementById("idr").value;
            var varID = '';
            for (var i = 1; i <jum; i++) {
              varID +='#jtl'+i+',#jtb'+i+',';
            }

            var jumvar = varID.length-1;
            varID = varID.substr(0,jumvar);

            $(varID).datepicker({
              format: "yyyy/mm/dd",
              autoclose: true
            });

          }

           getpelanggan('<?php echo $data_pelanggan; ?>');
           function getpelanggan(a=''){
            document.getElementById('pelanggan').innerHTML='<option value="">Loading</option>';
            var wilayah = document.getElementById('wilayah').value;
             
            var data  = 'wilayah='+wilayah;

            console.log(data);
            $.ajax({
              type  : 'POST', 
              url   : '<?php echo site_url('Graceperiodjt/GetPelanggan') ?>',
              data    : data,
              success : function(obj) {

                var json = JSON.parse(obj); 
                var jum = json.data.length;

                var option='<option value="ALL">ALL</option>';
                if(jum>0){
                  for(var i=0; i<jum; i++){
                    if(a!==''){
                      if(json.data[i].KD_PLG.trim()==a){
                        var selected = 'selected';
                      }else{
                        var selected = '';
                      }
                    }else{
                      var selected = '';
                    }
                    option += '<option value="'+json.data[i].KD_PLG+'" '+selected+'>'+json.data[i].NM_PLG+'</option>';
                  }

                  document.getElementById('pelanggan').innerHTML=option;

                }else{
                  document.getElementById('pelanggan').innerHTML='<option value="">Data Tidak Ditemukan</option>';
                }

                return false
              }
            })
           }

          <?php
            if($module=='add'){
          ?>
              $(function () {
                $('form').bind('submit', function () {
                  $.ajax({
                    type: 'post',
                    url: '<?php echo site_url('Graceperiodjt/add') ?>',
                    data: $('form').serialize(),
                    success: function (data) {

                     var json = JSON.parse(data);                   

                      if(json.status=='success'){
                        window.location.href = "<?php echo site_url('Graceperiodjt/view/'); ?>/"+json.number;
                      }else{
                        alert(json.status);
                      }

                    }
                  });
                  return false;
                });
              });
          <?php
            }
            if($module=='view' && $cekapproved=='show'){
          ?>
              function approved(e){
                if (confirm("Apakah anda yakin ingin "+e+" transaksi ini?") == true) {
                  var data ='status='+e
                      data +='&number=<?php echo $data_number; ?>';

                    console.log(data);
                    $.ajax({
                      type: 'post',
                      url: '<?php echo site_url('Graceperiodjt/Approved') ?>',
                      data: data,
                      success: function (data) {
                        location.reload();
                      }
                    });
                    return false;
                }
              }
          <?php
            }
          ?>



        </script>
    <?php
      }
      if($module=='list' && $_SESSION["can_delete"]==true){
    ?>
        <script>
          function delete_data(e){
            let note = prompt("Catatan harus diisi:", "");
            if (note == null || note == "") {

            }else{
              var data  = 'number='+e;
                  data += '&note='+note;

              console.log(data);
              $.ajax({
                type: 'post',
                url: '<?php echo site_url('Graceperiodjt/DeleteData'); ?>',
                data: data,
                success: function (data) {
                  var json = JSON.parse(data);
                  if(json.hasil=='success'){
                    window.location.href = "<?php echo site_url('Graceperiodjt'); ?>";
                  }else{
                    alert('Data tidak dapat dihapus');
                  }
                }
              });
            }
          }
        </script>
    <?php
      }
      if($module=='list'){
    ?>
        <script type="text/javascript">
          function Sync(e){
            var data ='number='+e;
            console.log(data)
            $.ajax({
              type: 'post',
              url: '<?php echo site_url('Graceperiodjt/Sync') ?>',
              data: data,
              success: function (data) {
                alert(data);
              }
            });
          }
        </script>
    <?php
      }
    ?>


  </div>
</div>