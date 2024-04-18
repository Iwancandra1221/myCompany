<script>
    $(document).ready(function() {
      $('#TblUser').DataTable({
        "pageLength": 10,
        "aaSorting": [[ 1, "asc" ]]
      });
    });
 
  function edit_config(kdplg,divisi){ 
    $.ajax({ 
        type: 'POST', 
        url: '<?php echo site_url("LogCreditLimitmo/Update") ?>', 
        data: { kdplg: kdplg, divisi: divisi}, 
        dataType: 'json',
        success: function (data) { 
          if(data.result=='SUKSES'){
            alert(data.result+'\n'+data.message);
            location.reload();
          }
          else{
            alert(data.result+'\n'+data.message);
          }
        }
      });
  } 
</script>  

 

<div class="container">
    <div>
      <div class="row"> 
    </div> 

    <div class="form_title" style="text-align: center;"> ?php echo $title ?> </div> 
    <br>
    <table id="TblUser" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <thead>
        <tr>
          <th scope="col" width="15%" style="text-align:left">Log Date</th>
          <th scope="col" width="30%" style="text-align:left">Nama Pelanggan</th>
          <th scope="col" width="15%" style="text-align:left">Credit Limit</th>
          <th scope="col" width="15%" style="text-align:left">Total Piutang</th> 
          <th scope="col" width="15%" style="text-align:left">Sisa CL</th> 
          <th scope="col" width="5%" style="text-align:left"></th> 
        </tr>
      </thead>
      <tbody id="TblUserBody">
      <?php
        $x = 1;  
        foreach($result as $u) {
          $LogDate = $u->LogDate;
          $NmPlg = $u->NmPlg;
          $CreditLimit = number_format($u->CreditLimit,0);
          $TotalPiutang = number_format($u->TotalPiutang,0);
          $SisaCL =  number_format($u->SisaCL,0); 

          echo "<tr>"; 
          echo "<td >".date("d-M-Y h:i:s",strtotime($LogDate))."</td>";   
          echo "<td >".$NmPlg."</td>"; 
          echo "<td style='text-align:right'>".$CreditLimit."</td>";  
          echo "<td style='text-align:right'>".$TotalPiutang."</td>"; 
          // if ($TotalPiutang < 0)
          // {
          //   echo "<td style='text-align:right; color:green'><b>".$TotalPiutang."</b></td>"; }
          // else
          // {
          //   echo "<td style='text-align:right'>".$TotalPiutang."</td>"; 
          // }  
          echo "<td style='text-align:right'>".$SisaCL."</td>";  
          echo '<td>
                <button class="btn btn-sm btn-default" onclick="javascript:edit_config('."'".$u->KdPlg."','".$u->Divisi."'".')"><i class="glyphicon glyphicon-refresh"></i></button> 
                </td>';
          echo "</tr>";
          $x += 1;
        }
        echo "</tbody>"; ?>
    </table>
</div> 