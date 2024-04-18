  <script>
      $(document).ready(function() {
        $('#example').DataTable({
          "pageLength": 25
        });
      });

      $('#confirm-delete').on('show.bs.modal', function(e) {
          $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
          $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
      });
  </script>  


      <div class="container">
      <div class="page-title">MASTER DATABASE</div> 
      <?php if($access->can_create == 1) { ?>
        <a href="masterDb/insert_page">Insert New Database</a>
      <?php } ?>
      <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <?php 
          echo "<thead>";
          echo "<tr>";
          echo "<th>No</th>";
          // echo "<th>id</th>";
          echo "<th>Nama Database</th>";
          echo "<th>Branch ID</th>";
          echo "<th>Alamat Web Service</th>";
          echo "<th>Server</th>";
          echo "<th>Database</th>";
          //echo "<th>Status</th>";
          if($access->can_read == 1)
            echo "<th>View</th>";
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
             echo "<td>".$r->NamaDb."</td>"; 
             echo "<td>".$r->BranchId."</td>"; 
             echo "<td>".$r->AlamatWebService."</td>";
             echo "<td>".$r->Server."</td>";
             echo "<td>".$r->Database."</td>";
             //echo "<td>-</td>";

             if($access->can_read == 1)
                echo "<td><a href = 'masterDb/detail_page?id=".$r->DatabaseId."'><i class='glyphicon glyphicon-eye-open'></td>";

             if($access->can_update == 1)
                echo "<td><a href = 'masterDb/update_page?id=".$r->DatabaseId."'><i class='glyphicon glyphicon-pencil'></td>";
             if($access->can_delete == 1)
                echo "<td><a href = '#' data-href='masterDb/delete_data?id=".$r->DatabaseId."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->NamaDb."'><i class='glyphicon glyphicon-trash'></a></td>"; 
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
