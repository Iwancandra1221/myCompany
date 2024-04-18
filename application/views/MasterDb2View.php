<?php
  if(!isset($_SESSION['logged_in']) or $access->can_read != 1){
    redirect('main','refresh');
  }

?>
<script>
  $(document).ready(function() {
    $('#example').DataTable();
  } );

  $('#confirm-delete').on('show.bs.modal', function(e) {
      $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
      $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
  });

  $(document).ready(function () {
      $("#flash-msg").delay(1200).fadeOut("slow");
  });
</script>

    <div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
      <div style="padding: 5px;">
    <?php
        if (isset($_GET['insertsuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Inserted <strong>Successfully !</strong></div>";
        }

        if (isset($_GET['updatesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Updated <strong>Successfully !</strong></div>";
        }

        if (isset($_GET['deletesuccess'])) {
          echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>Data Removed <strong>Successfully !</strong></div>";
        }
    ?>
      </div>
    </div>
    <!-- Fixed navbar -->

    <div class="container">
    <?php if($access->can_create == 1) { ?>
      <a href="masterDb/insert_page">Insert New Database</a>
    <?php } ?>
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>No</th>";
        // echo "<th>id</th>";
        echo "<th>Branch ID</th>";
        echo "<th>Nama Database</th>";
        echo "<th>Alamat Web Service</th>";
        echo "<th>Server</th>";
        echo "<th>Database</th>";
        echo "<th>Status</th>";
        if($access->can_update == 1)
          echo "<th>Edit</th>";
        if($access->can_delete == 1)
          echo "<th>Delete</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $i = 1;
        foreach($result as $r) {
           echo "<tr>"; 
           echo "<td>".$i."</td>";
           // echo "<td>".$r->ID."</td>";
           echo "<td>".$r->BranchId."</td>"; 
           echo "<td>".$r->NamaDb."</td>"; 
           echo "<td>".$r->AlamatWebService."</td>";
           echo "<td>".$r->Server."</td>";
           echo "<td>".$r->Database."</td>";
           if($r->IsPajak == 0){
            echo "<td>Non-Pajak</td>";
           }
           else{
            echo "<td>Pajak</td>";
           }
            if($access->can_update == 1)
              echo "<td><a href = 'masterDb/update_page?id=".$r->ID."'><i class='glyphicon glyphicon-pencil'></td>";
            if($access->can_delete == 1)
              echo "<td><a href = '#' data-href='masterDb/delete_data?id=".$r->ID."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->NamaDb."'><i class='glyphicon glyphicon-trash'></a></td>"; 
           echo "</tr>";
           $i += 1;
        }
        echo "</tbody>"; ?>
    </table>
    </div> <!-- /container -->
    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Delete Confirmation
            </div>
            <div class="modal-body">
              <p>You are about to delete <b><i class="title"></i></b> record, this procedure is irreversible.</p>
              <p>Do you want to proceed?</p>
              <!-- <p class="debug-url"></p> -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
  </div>
  <script>
      $('#confirm-delete').on('show.bs.modal', function(e) {
          $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
          
          $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
      });

       $('#confirm-delete').on('show.bs.modal', function(e) {
            var data = $(e.relatedTarget).data();
            $('.title', this).text(data.recordTitle);
        });
    </script>
