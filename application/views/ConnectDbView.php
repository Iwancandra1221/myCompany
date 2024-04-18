<script>
  $( document ).ready(function(){
    $(".label_help").hide();
      /*$.post("masterDbCtr/getListDb",'',
        function(data){
          var sel = $("#selDb");
          sel.empty();
          for (var i=0; i<data.length; i++) {
            alert(data[i].NamaDb);
            sel.append('<option value="' + data[i].ID + '">' + data[i].NamaDb + ' - ' + data[i].AlamatWebService + ' (Server : ' + data[i].Server + ', Database : ' + data[i].Database + ')</option>');
          }
        }, "json"
      );*/
    $("#img_help").click(function(){
      $(".label_help").toggle();
    });
    $(".label_help").click(function(){
      $(this).hide();
    });
  });
</script>
<div class="container" style="margin-top:30px;">
  <?php echo form_open('ConnectDB/connect'); ?>
    <div class="form-group">
      <label>Select Database</label>
      <select class="form-control" id="selDb" name="selDb">
      <?php
        foreach($db as $d) {
          echo('<option value="'.$d->DatabaseId.'">'.$d->NamaDb.' - '.$d->AlamatWebService.' (Server : '.$d->Server.', Database : '.$d->Database.')</option>');
        }
      ?>
      </select>
      <small id="info" class="form-text text-muted">Please select database to proceed</small>
    </div>
    <div class="form-group">
      <input type='checkbox' name='chkDefaultDb' id="chkDefaultDb">&nbsp;Set as Default Database&nbsp;<img src="<?php echo(base_url())?>images/help.png" alt="help" height="20" width="20" id="img_help">      
    </div>
    <div class="form-group">
      <div class="label_help">
        Jika diset sebagai Default Database, maka user akan secara otomatis dihubungkan ke database tersebut pada saat login.
        Tidak perlu memilih database lagi.
      </div>
    </div>
    <input type="submit" class="btn btn-primary" value="Proceed" id="btnProses">
  <?php echo form_close(); ?>
</div>