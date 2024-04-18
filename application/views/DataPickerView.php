<script>
  $(document).ready(function() {
    $('#example').DataTable();
    $("#loading").hide();
    $("#disablingDiv").hide();

    $("#btnSearch").click(function(){
        var keyword = $("#cari").val().toUpperCase();
        $(".brs").show();

        $(".brs").each(function(){
          ketemu = false;
          $(".col", this).each(function () {
            if (ketemu==false) {
              str = $(this).html().toUpperCase();
              if (str.indexOf(keyword) >= 0) {
                //alert(str);
                ketemu = true;
              }
            }
          });
          if (ketemu) {
            $(this).show(); 
          } else {
            $(this).hide(); 
          }
        })
    });
  });

  function sendValue(data1,data2,data3)
  {
    var parentId = <?php echo json_encode($_GET['id']); ?>;
    window.opener.updateValue(parentId, data1, data2, data3);
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
    display:none;
  }
  .loader {
      position: absolute;
      left: 50%;
      top: 50%;
      z-index: 1;
      width: 150px;
      height: 150px;
      margin: -75px 0 0 -75px;
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite;
      animation: spin 2s linear infinite;
      display:none;
  }

  @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
  }
  .header {
    position:fixed;top:0px;left:0px;width:100%;height:40px;background-color:navy;padding:5px;
    text-align:right;
  }
  .table {
    margin-top:50px;
  }
</style>


<!-- loading div -->
<div id="disablingDiv" class="disablingDiv">
</div>
<div id="loading" class="loader">
</div>
<!-- end loading div -->

<div class="container">
  <div class="header" style="display:none;"><input type="text" name="cari" id="cari">&nbsp;&nbsp;<button name="btnSearch" id="btnSearch">Search</button></div>
  <div class="table">
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
  <?php 
      if ($tipe=="EMPLOYEE") {

        echo "<thead>";
        echo "  <tr>";
        echo "    <th>&nbsp</th>";  
        echo "    <th>Nama</th>";
        echo "    <th class='hideOnMobile'>Email</th>";
        echo "    <th class='hideOnMobile'>UserID</th>";
        echo "  </tr>";
        echo "</thead>";
        echo "<tbody>";

        for($r=0;$r<count($row);$r++) {
          echo "<tr class='brs'>"; 
      ?>
          <td>
            <button onClick="sendValue('<?php echo $row[$r]["UserID"] ?>','<?php echo $row[$r]["EmployeeName"] ?>','<?php echo $row[$r]["Email"] ?>')">
              Select
            </button>
          </td>
      <?php
          echo "  <td class='col'>".$row[$r]["EmployeeName"]."</td>";
          echo "  <td class='col hideOnMobile'>".$row[$r]["Email"]."</td>";
          echo "  <td class='col hideOnMobile'>".$row[$r]["UserID"]."</td>"; 
          echo "</tr>";
        }
      }
    ?>
  <?php 
      if ($tipe=="DEALER") {

        echo "<thead>";
        echo "  <tr>";
        echo "    <th>&nbsp</th>";  
        echo "    <th>Nama</th>";
        echo "    <th class='hideOnMobile'>Kode</th>";
        echo "    <th>Wilayah</th>";
        echo "    <th class='hideOnMobile'>Alamat</th>";
        echo "  </tr>";
        echo "</thead>";
        echo "<tbody>";

        for($r=0;$r<count($row);$r++) {
          echo "<tr class='brs'>"; 
      ?>
          <td>
            <button onClick="sendValue('<?php echo $row[$r]["KD_PLG"] ?>','<?php echo $row[$r]["NM_PLG"] ?>','<?php echo $row[$r]["ALM_PLG"] ?>')">
              Select
            </button>
          </td>
      <?php
          echo "  <td class='col'>".$row[$r]["NM_PLG"]."</td>";
          echo "  <td class='col hideOnMobile'>".$row[$r]["KD_PLG"]."</td>";
          echo "  <td class='col'>".$row[$r]["WILAYAH"]."</td>"; 
          echo "  <td class='col hideOnMobile'>".$row[$r]["ALM_PLG"]."</td>"; 
          echo "</tr>";
        }
      }
    ?>
    <?php
      if ($tipe=="SALESMAN") {
        echo "<thead>";
        echo "  <tr>";
        echo "    <th>&nbsp</th>";  
        echo "    <th>Kode</th>";
        echo "    <th>Nama</th>";
        echo "    <th>Alamat</th>";
        echo "  </tr>";
        echo "</thead>";
        echo "<tbody>";

        for($r=0;$r<count($row);$r++) {
          echo "<tr class='brs'>"; 
      ?>
          <td>
            <button onClick="sendValue('<?php echo $row[$r]["KodeSalesman"] ?>','<?php echo $row[$r]["NamaSalesman"] ?>','<?php echo $r->WilayahSalesman ?>')">
              Select
            </button>
          </td>
      <?php
          echo "  <td class='col'>".$r->KodeSalesman."</td>"; 
          echo "  <td class='col'>".$r->NamaSalesman."</td>";
          echo "  <td class='col'>".$r->WilayahSalesman."</td>";
          echo "</tr>";
        }
      }
      echo "</tbody>"; ?>
    </table>
  </div>
</div> <!-- /container -->


