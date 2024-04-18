<script>
  $(document).ready(function() {
    $('#example').DataTable();

  } );
</script>
<style>
</style>

<div class="container">
  <?php echo form_open('masterDb/insert_data'); ?>
      <div class="row">
        <div class="col-3 col-m-4">Nama Database</div>
        <div class="col-9 col-m-8">
          <input type="text" class="form-control" name="txtNamaDb" id="txtNamaDb" placeholder="Input Nama Database" required>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Cabang</div>
        <div class="col-9 col-m-8">
          <select name="txtBranchId" id="txtBranchId">
            <?php foreach($branches as $b) { 
              echo("<option value='".$b->branch_id."'>".$b->branch_name."</option>");
            }?>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Server</div>
        <div class="col-9 col-m-8">
          <input type="text" class="form-control" name="txtServer" id="txtServer" placeholder="Input Server" required>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Database</div>
        <div class="col-9 col-m-8">
          <input type="text" class="form-control" name="txtDb" id="txtDb" placeholder="Input Database" required>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Alamat Web Service</div>
        <div class="col-9 col-m-8">
          <input type="text" class="form-control" name="txtAlamatWS" id="txtAlamatWS" placeholder="Input Alamat Web Service" required>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Alamat Web Service Java</div>
        <div class="col-9 col-m-8">
          <input type="text" class="form-control" name="txtAlamatWSJava" id="txtAlamatWSJava" placeholder="Input Alamat Web Service Java" required>
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Tipe Database</div>
        <div class="col-9 col-m-8">
          <select name="txtDbType" id="txtDbType"> 
            <?php foreach($databasetype as $type) { 
              echo("<option value='".$type->ConfigValue."'>".$type->ConfigValue."</option>");
            }?>
          </select>          
        </div>
      </div>
      <div class="row">
        <div class="col-3 col-m-4">Location Code</div>
        <div class="col-9 col-m-8">
          <input type="text" class="form-control" name="txtLoc" id="txtLoc" placeholder="Input Location Code" >
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-m-12">
          <input type="submit" class="btn btn-primary" value="Submit">
          <input type="button" class="btn btn-danger" onclick="location.href = '../masterDb';" value="Cancel">
        </div>
      </div>
  <?php echo form_close(); ?>
</div>