<?php
  if(!isset($_SESSION['logged_in'])){
    redirect('main','refresh');
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title></title>
    <?php include ('template/commonhead.php'); ?>

    <!-- css for loading -->
    <style type="text/css">
      .disablingDiv{
        z-index:1;
         
        /* make it cover the whole screen */
        position: fixed; 
        top: 0%; 
        left: 0%; 
        width: 100%; 
        height: 100%; 
        overflow: hidden;
        margin:0;
        /* make it white but fully transparent */
        background-color: white; 
        opacity:0.5;  
      }
      .loader {
          position: absolute;
          left: 50%;
          top: 50%;
          z-index: 1;
          margin: -75px 0 0 -75px;
          border: 16px solid #f3f3f3;
          border-radius: 50%;
          border-top: 16px solid #3498db;
          width: 120px;
          height: 120px;
          -webkit-animation: spin 2s linear infinite;
          animation: spin 2s linear infinite;
      }

      @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
      }
    </style>

    <script>
      $(document).ready(function() {
        $("#loading").hide();
        $("#disablingDiv").hide();
      } );

      function sendValue(kd_plg,nm_plg,alm_plg)
      {
        var parentId = <?php echo json_encode($_GET['id']); ?>;
        window.opener.updateValue(parentId, kd_plg, nm_plg, alm_plg);
        window.close();
      }

      $(document).ajaxStart(function() {
        $("#disablingDiv").show();
        $("#loading").show();
      });

      $(document).ajaxStop(function() {
        $("#loading").hide();
        $("#disablingDiv").hide();
      });
    </script>
  </head>

  <body>
    <!-- loading div -->
    <div id="disablingDiv" class="disablingDiv">
    </div>
    <div id="loading" class="loader">
    </div>
    <!-- end loading div -->

    <div class="container">
      <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="table">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "<th>&nbsp</th>";  
        echo "<th>No</th>";
        // echo "<th>id</th>";
        echo "<th>Kode</th>";
        echo "<th>Nama</th>";
        echo "<th>Alamat</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $i = 1;
        foreach($row as $r) {
          echo "<tr>"; 
        ?>
          <td>
            <button onClick="sendValue('<?php echo $r->CabangID ?>','<?php echo $r->CabangName ?>','<?php echo $r->Alamat ?>')">
              Select
            </button>
          </td>
        <?php
          echo "<td>".$i."</td>";
          echo "<td>".$r->CabangID."</td>"; 
          echo "<td>".$r->CabangName."</td>";
          echo "<td>".$r->Alamat."</td>";

          echo "</tr>";
          $i += 1;
        }
        echo "</tbody>"; ?>
    </table>
    </div> <!-- /container -->

  </body>
</html>
