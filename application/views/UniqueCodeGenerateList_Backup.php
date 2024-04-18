<script>
    $(document).ready(function() {
      $('#TblSN').DataTable({
        "pageLength": 25
      });
    });
</script>  

<style>
  .glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }
  .merah { color:#c91006; }
  .hijau { color:#0ead05;}
  .generate-button {
    margin-left: 0px;
    margin-bottom:30px;
  }
</style>

<div class="container">
  <div>
    <div class="page-title">UNIQUE CODE GENERATOR</div>
    <div class="row generate-button">
      <a href="UniqueCodegenerator/GenerateForm"><button>Generate Unique Code</button></a>
      <!-- <a href="UniqueCodegenerator/GenerateForm" target="_blank"><button>Generate Unique Code</button></a> -->
    </div>
  </div>
  <div>
    <table id="TblSN" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <thead>
        <tr>
          <th width="5%"  class="hideOnMobile">ID</th>
          <th width="20%"  class="hideOnMobile">DATE</th>
          <th width="10%" class="hideOnMobile">Generated By</th>
          <th width="10%" class="hideOnMobile">Serial No. Min</th>
          <th width="10%" class="hideOnMobile">Serial No. Max</th>
          <th width="30%" class="hideOnMobile">Product ID</th>
          <th width="15%" class="hideOnMobile">Result</th>
          <!-- <th width="20%" class="hideOnMobile">Action</th> -->
          <th width="100%" class="colMobile">History</th>
          <th width="5%">Delete</th>
        </tr>
      </thead>
      <tbody id="TblSN">
      <?php
        foreach($SNList as $sn) {
          $UCID = $sn->LogId;
          $DATE = $sn->LogDate;
          $CBY = $sn->CreatedBy;
          $SNMIN = $sn->SerialNoMin;
          $SNMAX = $sn->SerialNoMax;
          $PRODUCT = $sn->ProductID;
          $RESULT = $sn->Description;

          $ACTION = "";
          // if($_SESSION["can_read"] == 1) {
          //   if ($u->AlternateID!=0) {
          //     $ACTION .= "<a href = 'UserControllers/View/".$u->AlternateID."'><i class='glyphicon glyphicon-search'></i></a>";
          //   } else {
          //     $ACTION .= "<a href = 'UserControllers/View2/".urlencode($u->UserEmail)."'><i class='glyphicon glyphicon-search'></i></a>";
          //   }
          // }
          // if($_SESSION["can_update"] == 1) {
          //   if ($u->AlternateID!=0) {
          //     $ACTION .= "<a href = 'UserControllers/View/".$u->AlternateID."/1/1'><i class='glyphicon glyphicon-edit hijau'></i></a>";
          //   } else {
          //     $ACTION .= "<a href = 'UserControllers/View2/".urlencode($u->UserEmail)."/1/1'><i class='glyphicon glyphicon-edit hijau'></i></a>";              
          //   }
          // }
          // if($_SESSION["can_delete"] == 1) {
          //   if ($u->AlternateID!=0) {
          //     $ACTION .= "<a href = 'UserControllers/Disable/".$u->AlternateID."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$u->UserName."'><i class='glyphicon glyphicon-trash merah'></i></a>";
          //   } else {
          //     $ACTION .= "<a href = 'UserControllers/Disable2/".urlencode($u->UserEmail)."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$u->UserName."'><i class='glyphicon glyphicon-trash merah'></i></a>";              
          //   }
          // }
          $MOBILE = "#".$UCID."<br>".$DATE."<br>".$CBY."<br>".$SNMIN."<br>".$SNMAX."<br>".$PRODUCT."<br>".$RESULT;

          echo "<tr id='row_".$UCID."'>"; 
          echo "<td class='hideOnMobile'>".$UCID."</td>";
          echo "<td class='hideOnMobile'>".$DATE."</td>";
          echo "<td class='hideOnMobile'>".$CBY."</td>"; 
          echo "<td class='hideOnMobile'>".$SNMIN."</td>"; 
          echo "<td class='hideOnMobile'>".$SNMAX."</td>";
          echo "<td class='hideOnMobile'>".$PRODUCT."</td>";
          echo "<td class='hideOnMobile'>".$RESULT."</td>";
          echo "<td>";
			  if($CBY==$_SESSION["logged_in"]["useremail"]){
				echo "<input type='button' value='Delete' onclick='javascript:HideCode(".$UCID.")'>";
			  } else echo "-";
		  echo "</td>";
          echo "<td width='100%' class='colMobile'>".$MOBILE."</td>";
          echo "</tr>";

        }
        echo "</tbody>"; ?>
    </table>
  </div>
</div> <!-- /container -->

<script>
	function HideCode(LogID){
		var c = confirm("Delete Code Ini??");
		if (c == true) {
			$.ajax({
				url:'UniqueCodegenerator/Hide/'+LogID,
				type:'POST',
				success:function(msg){
					if(msg== true){
						 $('#row_'+LogID).hide();
					}
					
				}
			});
		}
	}
</script>