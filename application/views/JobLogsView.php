<script>
  $(document).ready(function() {

    $('#dp1').datepicker({
      format: "mm/dd/yyyy",
      autoclose: true
    }).on('changeDate', function(e) { //changeDate
      var StartDt = $('#dp1').datepicker('getDate');
      $('#dp2').datepicker("setStartDate", StartDt);
    });
    
    $('#dp2').datepicker({
      format: "mm/dd/yyyy",
      autoclose: true
    }).on('changeDate', function(e) { //changeDate
      var EndDt = $('#dp2').datepicker('getDate');
      $('#dp1').datepicker("setEndDate", EndDt);
    });
 
    $("#dp2").datepicker("setDate", new Date());
    var date = new Date(); 
    date.setDate(date.getDate() - 7);
    $("#dp1").datepicker("setDate", date);

    $("#txtJobsId").change(function() {
      let text = $("#txtJobsId").val();
      const myArray = text.split("#");  
       $("#txtDes").text(myArray[1]); 
       $("#txtFunction").text(myArray[2]); 
    });

  } ); 

  function viewdata()
  {      
    if ($("#dp1").val()=="" || $("#dp2").val()=="")  
      alert("Tanggal Periode PO Tidak Boleh Kosong"); 
    else if ($("#txtJobsId").val()=="" || $("#txtJobsId").val()==null)  
      alert("Jobs Id Belum di Pilih"); 
    else
    { 
      var table = $('#table_detail').DataTable(); 
      let text = $("#txtBranchId").val();
      const myArray = text.split("#"); 
      var svr = myArray[1];
      var db = myArray[2]; 
      var url = myArray[3]; 

      let text2 = $("#txtJobsId").val();
      const myArray2 = text2.split("#"); 
      var jobsid = myArray2[0];  

      var dp1 = $("#dp1").val(); 
      var dp2 = $("#dp2").val();

      table.clear().draw();
      table.destroy();
      $('#table_detail').dataTable({searching: false, paging: false, info: false, order: false});
      $.ajax({
        type: 'GET', 
        url: '<?php echo site_url("Jobs/ViewLogsByBranch?url='+url+'&svr='+svr+'&db='+db+'&dp1='+dp1+'&dp2='+dp2+'&jobsid='+jobsid+'") ?>',
        dataType: 'json',
        success: function (data){ 
          if(data.length>0){ 
            for (var i = 0; i < data.length; i++) { 
              if (data[i].job_run_status=="SUCCESS")
                var x = "green";
              else
                var x = "red";
              $('#table_detail').DataTable().row.add([
                '<p >'+data[i].job_run_start+'</p>',
                '<p >'+data[i].job_run_end+'</p>',
                '<p >'+data[i].job_schedule_type+'</p>',
                '<p style="color:'+x+';">'+data[i].job_run_status+'</p>',
                '<p style="color:red;">'+data[i].job_run_error+'</p>'
                ]).draw();  
            } 
          } 
        }
      }); 
    } 
  }  

</script>
<style>
</style>

<div class="container">
  <?php echo form_open('Jobs/insert_data'); ?>
      <div class="row">
        <div align="center">
            <label>
              JOB LOGS
            </label >
        </div> 
      </div> 



    <div class="row ">
      <div class="col-2 col-m-4">Periode PO</div>
      <div class="col-2 col-m-4 date">
        <input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" required>
      </div>
      <div class="col-1 col-m-1">SD</div>
      <div class="col-2 col-m-4 date">
        <input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" required>
      </div>
    </div>

      <div class="row">
        <div class="col-2 col-m-4">BRANCH</div>
        <div class="col-4 col-m-4">
          <select  class="form-control" name="txtBranchId" id="txtBranchId">
            <option value="">Pilih Branch</option> 
            <?php foreach($ListBranches as $b) { 

              if ($b->BranchID == $BranchId)
              {
                echo("<option selected value='".$b->BranchID."#".$b->Server."#".$b->Database."#".$b->AlamatWebService."'>".$b->BranchName."</option>");  
              }
              else
              {
                echo("<option value='".$b->BranchID."#".$b->Server."#".$b->Database."#".$b->AlamatWebService."'>".$b->BranchName."</option>");  
              }
            }?>
          </select>
        </div>
      </div> 

 
      <div class="row">
        <div class="col-2 col-m-4">ID JOB</div>
        <div class="col-4 col-m-4">
          <select  class="form-control" name="txtJobsId" id="txtJobsId">
            <option value="">Pilih Job</option> 
            <?php foreach($ListJobs as $b) { 
              if ($b->job_id == $JobsID)
              {
                echo("<option selected value='".$b->job_id."#".$b->job_description."#".$b->job_function."'>".$b->job_id."</option>");
              }
              else
              {
                echo("<option value='".$b->job_id."#".$b->job_description."#".$b->job_function."'>".$b->job_id."</option>");
              }
            }?>
          </select>
        </div>  
        <div class="col-2 col-m-4">
          <button type="button" id="btnSave" onclick="viewdata()" >VIEW LOGS</button>  
        </div>
      </div> 
      <div class="row">
        <div class="col-2 col-m-4">DESCRIPTION</div>
          <div class="col-4 col-m-4">
            <label id="txtDes" name="txtDes" > 
               <?php foreach($ListJobs as $b) { 
                  if ($b->job_id == $JobsID)
                  {
                    echo($b->job_description);
                  } 
                }?>
            </label>
          </div>
      </div> 
      <div class="row">
        <div class="col-2 col-m-4">FUNCTION</div>
          <div class="col-4 col-m-4">
            <label id="txtFunction" name="txtFunction" >
               <?php foreach($ListJobs as $b) { 
                  if ($b->job_id == $JobsID)
                  {
                    echo($b->job_function);
                  } 
                }?>
            </label>
          </div>
      </div>  
      <div class="row">  
        <div class="col-11 col-m-8"> 
          <table id="table_detail" class="table table-bordered" cellspacing="0" cellpadding="5px;" width="100%" summary="table"> 
               <?php 
                  echo "<thead>";
                  echo "<tr>";
                  echo "<th>Run Start</th>";
                  echo "<th>Run End</th>"; 
                  echo "<th>Type</th>";    
                  echo "<th>Run Status</th>";     
                  echo "<th>Error</th>";   
                  echo "</tr>";
                  echo "</thead>";
                  echo "<tbody>";
    
                  $i = 1;
                  if ($ListLog!=null)
                  {
                    for ($i=0; $i < count($ListLog); $i++) {  
                      echo "<tr>";  
                      echo "<td>".$ListLog[$i]['job_run_start']."</td>"; 
                      echo "<td>".$ListLog[$i]['job_run_end']."</td>";   
                      echo "<td>".$ListLog[$i]['job_schedule_type']."</td>";  
                      if ($ListLog[$i]['job_run_status']=="SUCCESS")
                        echo "<td> <p style='color:green;'>".$ListLog[$i]['job_run_status']."</p></td>"; 
                      else
                        echo "<td> <p style='color:red;'>".$ListLog[$i]['job_run_status']."</p></td>"; 
                      echo "<td> <p style='color:red;'>".$ListLog[$i]['job_run_error']."</p></td>";
                      echo "</tr>"; 
                    } 
                  }
                  echo "</tbody>"; 
                ?>
          </table> 
      </div>
  <?php echo form_close(); ?>
</div>