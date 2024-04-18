<script>
  $(document).ready(function() {
    $('#example').DataTable();

  } );
</script>

<div class="container">
  <?php echo form_open('masterDb/update_data'); ?>
      <input type="hidden" name="hdnId" value="<?php echo $row->DatabaseId; ?>">
      <div class="form-group">
        <label>Branch ID</label>
        <input type="text" class="form-control" name="txtBranchId" id="txtBranchId" aria-describedby="emailHelp" placeholder="Edit Branch Id" required value="<?php echo $row->BranchId; ?>">
        <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
      </div>
      <div class="form-group">
        <label>Nama Database</label>
        <input type="text" class="form-control" name="txtNamaDb" id="txtNamaDb" placeholder="Edit Nama Database" required value="<?php echo $row->NamaDb; ?>">
      </div>
      <div class="form-group">
        <label>Alamat Web Service</label>
        <input type="text" class="form-control" name="txtAlamatWS" id="txtAlamatWS" placeholder="Edit Alamat Web Service" required value="<?php echo $row->AlamatWebService; ?>">
      </div>
      <div class="form-group">
        <label>Alamat Web Service Java</label>
        <input type="text" class="form-control" name="txtAlamatWSJava" id="txtAlamatWSJava" placeholder="Edit Alamat Web Service Java" required value="<?php echo $row->AlamatWebServiceJava; ?>">
      </div>
      <div class="form-group">
        <label>Server</label>
        <input type="text" class="form-control" name="txtServer" id="txtServer" placeholder="Edit Server" required value="<?php echo $row->Server; ?>">
      </div>
      <div class="form-group">
        <label>Database</label>
        <input type="text" class="form-control" name="txtDb" id="txtDb" placeholder="Edit Database" required value="<?php echo $row->Database; ?>">
      </div>

      <div class="form-group">
        <label>Tipe Database</label>
          <div> 
            <select name="txtDbType" id="txtDbType"> 
              <?php foreach($databasetype as $type) { 
                if ($row->DatabaseType==$type->ConfigValue)  
                  echo("<option value='".$type->ConfigValue."' selected>".$type->ConfigValue."</option>");
                else
                  echo("<option value='".$type->ConfigValue."'>".$type->ConfigValue."</option>");
              }?>
            </select>   
          </div>       
      </div> 

      <div class="form-group"> 
        <label>Location Code</label>
        <input type="text" class="form-control" name="txtLoc" id="txtLoc" placeholder="Input Location Code" value="<?php echo $row->LocationCode; ?>">
      </div> 

      <input type="submit" class="btn btn-primary" value="Submit">
      <input type="button" class="btn btn-danger" onclick="location.href = '../masterDb';" value="Cancel">
  <?php echo form_close(); ?>
</div> <!-- /container -->