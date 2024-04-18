<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('main','refresh');
  }

  // change here -- hardcode for database connection
  $username_db = 'sa';
  $password_db = 'Sprite12345';

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Request List | Credit Limit Application</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url('dist/css/bootstrap.min.css');?>" rel="stylesheet">
    <link href="<?php echo base_url('dist/css/datatables.bootstrap.min.css');?>" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="<?php echo base_url('assets/css/ie10-viewport-bug-workaround.css');?>" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo base_url('dist/css/navbar-fixed-top.css');?>" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?php echo base_url('assets/js/ie-emulation-modes-warning.js');?>"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.min.js');?>"></script>
    <script src="<?php echo base_url('dist/js/datatables.bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>
    <script src="<?php echo base_url('dist/js/bootstrap.min.js');?>"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo base_url('assets/js/ie10-viewport-bug-workaround.js');?>"></script>

    <!-- untuk number formatting -->
    <script src="<?php echo base_url('dist/js/jquery.number.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.number.min.js');?>"></script>

    <!-- untuk date formatting -->
    <script src="<?php echo base_url('dist/js/jquery.dateformat.js');?>"></script>
    <script src="<?php echo base_url('dist/js/jquery.dateformat.min.js');?>"></script>

    <script>
      $(document).ready(function() {
        $('#mainTable').DataTable({
          aaSorting: [[ 4, 'desc']]
        });
           
      });

      function getDetail(id){
        var canApp = 0;
        $('#btnApprove').hide();
        $('#btnReject').hide();
        $.ajax({
          // url : "CustomerPickerCtr/getData/" + kd_plg,
          url : "getApproverData",
          type: "POST",
          data: {aReqNo : id},
          dataType: "JSON",
          success: function(data)
          {
              // alert(data.Request_No);
              $('[name="txtRequestNo"]').val(data[0]["Request_No"]);
              $('[id="tdReqBy"]').html(data[0]["Request_By"]);
              $('[id="tdReqOn"]').html($.format.date(data[0]["Request_Time"], "dd-MMM-yyyy HH:mm"));
              $('[id="tdKdPlg"]').html(data[0]["Request_For_NmPlg"]+" "+data[0]["Request_For_KdPlg"]+"");
              $('[id="tdNmPlg"]').html(data[0]["Request_For_NmPlg"]);
              $('[id="tdPrevCL"]').html($.number(data[0]["CL_Previous"]));
              $('[id="tdReqCL"]').html($.number(data[0]["CL_Requested"])+" (required approval : "+ $.number(data[0]["Approval_Required"]) +")");
              $('[id="tdDiv"]').html(data[0]["Division"]);
              // $('[name="txtAlamatPlg"]').val(data.alm_plg);
              $("#tblApprover tbody tr").remove();
              for(var i=0;i<data.length;i++)
              {
                  var tr="<tr>";;
                  var td1="<td>"+data[i]["Approved_By"]+"</td>";

                  if(data[i]["Approval_Status"] == 1){
                    var td2="<td>No Response Yet</td>";
                  }
                  else if(data[i]["Approval_Status"] == 3){
                    var td2="<td>Approved</td>";
                  } 
                  else if(data[i]["Approval_Status"] == 4){
                    var td2="<td>Rejected</td>";
                  }
                  
                  if(data[i]["Approved_Time"] == null){
                    var td3="<td>&nbsp;</td>";
                  }
                  else{
                    var td3="<td>"+$.format.date(data[i]["Approved_Time"], "dd-MMM-yyyy HH:mm")+"</td>";
                  }
                  
                  $("#tblApprover tbody").append(tr+td1+td2+td3);

                  if(data[i]["Approved_By"] == '<?php echo $_SESSION['logged_in']['useremail'];?>' && data[i]["Approval_Status"] == 1 && data[i]["Last_Status"] != 3 && data[i]["Last_Status"] != 4){
                    $('#btnApprove').show();
                    $('#btnReject').show();
                  }
              }
          },
          error: function (jqXHR, textStatus, errorThrown)
          {
              alert('Error get data from ajax');
          }
        });
      }

      function rejectRequest(reqno){
        // var r = confirm("Are You Sure Want to Reject Change Limit Request No "+reqno+" ?");
        // if (r == true) {
        //     window.location.href = 'rejectRequest?reqno=' + reqno;
        // }
        document.getElementById('txtRequestNo2').value = reqno;
      }

      $(document).ready(function () {
          $("#flash-msg").delay(1200).fadeOut("slow");
      });
    </script>
  </head>

  <body>
    <!-- Alert Model -->
    <div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
      <div style="padding: 5px;">
    <?php
        if (isset($_GET['approvesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Request Approved <strong>Successfully !</strong></div>";
        }

        if (isset($_GET['rejectsuccess'])) {
          echo "<div class='alert alert-warning' id='flash-msg' style='float:auto'>Request Rejected <strong>Successfully !</strong></div>";
        }
    ?>
      </div>
    </div>

    <?php include ('template/menubar.php'); ?>


    <div class="container">
      <label>Credit Limit Adjustment Request List</label>
      <table id="mainTable" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="Credit Limit Adjustment Request List">
        <thead>
          <tr>
            <th scope="col">Request No</th>
            <th scope="col">Request By</th>
            <th scope="col">Request For</th>
            <th scope="col">Division</th>
            <th scope="col">Request Time</th>
            <th scope="col">Previous Limit</th>
            <th scope="col">New Limit Requested</th>
            <th scope="col">Last Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach($result as $r) {
               echo "<tr>";
               echo "<td>".$r->Request_No."</td>";
               echo "<td>".$r->Request_By."</td>"; 
               echo "<td>".$r->Request_For_NmPlg." [ Kode :".$r->Request_For_KdPlg." ]</td>";
               echo "<td>".$r->Division."</td>";
               echo "<td>".date('d-F-Y H:i',strtotime($r->Request_Time))."</td>";
               echo "<td>".number_format($r->CL_Previous)."</td>";
               echo "<td>".number_format($r->CL_Requested)."</td>";
               if($r->Last_Status == 1){
              ?>
                <td><a href='#' data-toggle='modal' data-target='#detailRequest' onclick='getDetail("<?php echo $r->Request_No; ?>");'>Awaiting For Approval</a></td>
              <?php
               }
               else if($r->Last_Status == 2){
              ?>
                <td><a href='#' data-toggle='modal' data-target='#detailRequest' onclick='getDetail("<?php echo $r->Request_No; ?>");'>Partially Approved</a></td>
              <?php
               }
               else if($r->Last_Status == 3){
              ?>
                <td><a href='#' data-toggle='modal' data-target='#detailRequest' onclick='getDetail("<?php echo $r->Request_No; ?>");'><font color='black'><strong>Fully Approved</strong></font></a></td>
              <?php
               }
               else if($r->Last_Status == 4){
              ?>
                <td><a href='#' data-toggle='modal' data-target='#detailRequest' onclick='getDetail("<?php echo $r->Request_No; ?>");'><font color='red'>Rejected</font></a></td>
              <?php
               }
               else if($r->Last_Status == 5){
              ?>
                <td><a href='#' data-toggle='modal' data-target='#detailRequest' onclick='getDetail("<?php echo $r->Request_No; ?>");'><font color='orange'>Partially Rejected</font></a></td>
              <?php
               }
               echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div> <!-- /container -->

    <!-- modal starts here -->
    <div class="modal fade" id="detailRequest" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Detail Request
            </div>
            <div class="modal-body">
              <form action="approveRequest" method="post" id="FrmApprove">
                <input type="hidden" name="hdnUsernameDB" value="<?php echo $username_db;?>">
                <input type="hidden" name="hdnPasswordDB" value="<?php echo $password_db;?>">
                <div class="form-group">
                  <label>Request No</label>
                  <input type="text" class="form-control" name="txtRequestNo" id="txtRequestNo" aria-describedby="emailHelp" readonly="">
                </div>
                <div class="form-group">
                  <label>Request Detail</label>
                  <table class="table">
                    <tr><td>Requested By</td><td>:</td><td id="tdReqBy"></td></tr>
                    <tr><td>Requested On</td><td>:</td><td id="tdReqOn"></td></tr>
                    <tr><td>Requested For Dealer</td><td>:</td><td id="tdKdPlg"></td></tr>
                    <!-- <tr><td>Nama Dealer</td><td>:</td><td id="tdNmPlg"></td></tr> -->
                    <tr><td>Previous Credit Limit</td><td>:</td><td id="tdPrevCL"></td></tr>
                    <tr><td>Requested Credit Limit</td><td>:</td><td id="tdReqCL"></td></tr>
                    <!-- <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> -->
                    <tr><td>Divisi</td><td>:</td><td id="tdDiv"></td></tr>
                  </table>
                </div>
                <div class="form-group">
                  <label>Waiting Approval From</label>
                  <table class="table" id="tblApprover">
                    <thead>
                      <tr>
                        <th>Approver</th><th>Approval Status</th><th>Action Time</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="btnApprove" onclick="document.getElementById('FrmApprove').submit();">Approve</button>
                <button type="button" class="btn btn-danger" id="btnReject" data-toggle='modal' data-target='#detailReasonReject' onclick="rejectRequest(document.getElementById('txtRequestNo').value)">Reject</button>
                <button type="button" class="btn btn-normal" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
  </div>

  <div class="modal fade" id="detailReasonReject" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Are You Sure Want to Reject this Request ?
            </div>
            <div class="modal-body">
              <form action="rejectRequest" method="post" id="FrmReject">
                <input type="hidden" id="txtRequestNo2" name="hdnReqNoReject">
                <div class="form-group">
                  <label>Reason</label>
                  <textarea class="form-control" name="txtReason" id="txtReason" placeholder="(optional) input rejection reason here ..."></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="document.getElementById('FrmReject').submit();">Yes</button>
                <button type="button" class="btn btn-normal" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
  </div>
    <!-- end of modal -->
  </body>
</html>