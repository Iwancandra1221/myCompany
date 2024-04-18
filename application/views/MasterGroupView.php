<script>
    $(document).ready(function() {
      $('#TblUser').DataTable({
        "pageLength": 25
      });
    });


    function toggleCheckbox(groupid,activeid){ 
      $.ajax({ 
        type: 'POST', 
        url: '<?php echo site_url("mastergroup/Update") ?>', 
        data: { GroupId: groupid, ActiveId: activeid}, 
        dataType: 'json',
        success: function (data) { 
          if(data.result=='SUKSES'){
            location.reload();
          }
          else{
            alert(data.result+'\n'+data.message);
          }
        }
      });
    }

</script>  

<style>
  .glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }
  .merah { color:#c91006; }
  .hijau { color:#0ead05;}
</style>

<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>

<div class="container">
    <div>
      <div class="row"> 
    </div>
    <div style="padding-bottom: 20px"> 
    <a href="<?php echo site_url('mastergroup/MsGroupSync');?>">
                <button type="button" name="btnsync" class="btn btn-custom-red">
                  Sync Master Group From ZEN
                </button>
    </a>  
    </div>

    <table id="TblUser" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <thead>
        <tr>
          <th scope="col" width="10%"  class="hideOnMobile">Group ID</th>
          <th scope="col" width="30%" class="hideOnMobile">Group Name</th>
          <th scope="col" width="20%" class="hideOnMobile">City</th>
          <th scope="col" width="10%" class="hideOnMobile">Branch ID</th> 
          <th scope="col" width="15%" class="hideOnMobile">Update By</th>
          <th scope="col" width="15%" class="hideOnMobile">Update Date</th> 
          <th scope="col" width="10%" class="hideOnMobile">Active</th>
        </tr>
      </thead>
      <tbody id="TblUserBody">
      <?php
        $x = 1;  
        foreach($groups as $u) {
          $GroupID = $u->GroupID;
          $Name = $u->Name;
          $City = $u->City;
          $BranchID = $u->BranchID;
          $STATUS = (($u->IsActive==1)? "AKTIF" : "TIDAK AKTIF"); 
 
          $ACTION = 
          (
            ($u->IsActive==1)? 
              "<label class='switch'> <input type='checkbox' checked onchange='toggleCheckbox(".'"'.$u->GroupID.'"'.",".'"'.$u->IsActive.'"'.")'> <span class='slider round'></span> </label>"  
            : 
              "<label class='switch'> <input type='checkbox' onchange='toggleCheckbox(".'"'.$u->GroupID.'"'.",".'"'.$u->IsActive.'"'.")'> <span class='slider round'></span> </label>"
          ); 

          echo "<tr>"; 
          echo "<td class='hideOnMobile'>".$GroupID."</td>";
          echo "<td class='hideOnMobile'>".$Name."</td>"; 
          echo "<td class='hideOnMobile'>".$City."</td>"; 
          echo "<td class='hideOnMobile'>".$BranchID."</td>";
          //echo "<td class='hideOnMobile'>".$STATUS."</td>"; 
          echo "<td class='hideOnMobile'>".$u->UpdatedBy."</td>"; 
          echo "<td class='hideOnMobile'>".date("d-M-Y",strtotime($u->UpdatedDate))."</td>";  
          echo "<td class='hideOnMobile'>".$ACTION."</td>";  
          echo "</tr>";
          $x += 1;
        }
        echo "</tbody>"; ?>
    </table>
</div> <!-- /container -->