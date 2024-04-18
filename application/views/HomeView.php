<script>
  $( document ).ready(function(){
      $.post("masterDbCtr/getListDb",'',
        function(data){
          var sel = $("#selDb");
          sel.empty();
          for (var i=0; i<data.length; i++) {
            sel.append('<option value="' + data[i].ID + '">' + data[i].NamaDb + ' - ' + data[i].AlamatWebService + ' (Server : ' + data[i].Server + ', Database : ' + data[i].Database + ')</option>');
          }
        }, "json"
      );
  });
</script>

<?php
  if (1==0)
  {
?>
    <div class="container">
      <form method="post" action="creditLimitCtr/index">
        <div class="form-group">
          <label>Select Database</label>
          <select class="form-control" id="selDb" name="selDb">
            
          </select>
          <small id="info" class="form-text text-muted">Please select database to proceed</small>
        </div>
        <input type="submit" class="btn btn-primary" value="Proceed" id="btnProses">
      </form>
    </div> <!-- /container -->
<?php
  }
?>
