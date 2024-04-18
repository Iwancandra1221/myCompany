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
  }

  @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
  }
</style>
<!--  -->

<script>
  var LoadUsers = function() {
      var tbody = "";
      var filter_nama = "";
      var filter_branch=$("#BranchID").val();

      $(".loading").show();
      var csrf_bit = $("input[name=csrf_bit]").val();
      $.post("<?php echo site_url('UserPicker/GetUsers'); ?>", {
        Branch  : filter_branch,
        Nama    : filter_nama,
        csrf_bit: csrf_bit
      }, function(data){
        if (data.error != undefined)
        {
          alert("hoho");
        }
        else
        {
          for(var i=0; i<data.length; i++)
          { 
            var j = i+1;
            tbody += "<tr>"; 
            tbody += " <td><button onClick=sendValue('"+data[i].UserEmail+"','"+data[i].branch_id+"')>Select</button></td>";
            tbody += " <td>"+j+"</td>";
            tbody += " <td>"+data[i].UserEmail+"</td>"; 
            tbody += " <td>"+data[i].UserName+"</td>";
            tbody += " <td>"+data[i].branch_id+"</td>";
            tbody += "</tr>";
          }
          $("#tbodyUsers").html(tbody);
        }
        $(".loading").hide();
      }
      ,'json', "");
  }

  $(document).ready(function(){
    LoadUsers();

    /*$("#BtnSearch").click(function(){
      LoadUsers();
    });*/

  });

  function sendValue(email, branchid)
  {
      var parentId = <?php echo json_encode($_GET['id']); ?>;
      window.opener.updateValue2(parentId, email, branchid);
      window.close();
  }

</script>


<div class="container">
    <select id="BranchID"></select>
      <!-- <div id="BtnSearch" style="width:100px;border:1px solid #ccc;">SEARCH</div> -->
    <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
      <?php 
        echo "<thead>";
        echo "<tr>";
        echo "  <th>&nbsp</th>";  
        echo "  <th>No</th>";
        echo "  <th>Email</th>";
        echo "  <th>Full Name</th>";
        echo "  <th>Branch ID</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody id='tbodyUsers'>";

        /*$i = 1;
        foreach($row as $r) {
           echo "<tr>"; 
           echo " <td><button onClick=sendValue('".$r->UserEmail."','".$r->branch_id."')>Select</button></td>";
           echo " <td>".$i."</td>";
           echo " <td>".$r->UserEmail."</td>"; 
           echo " <td>".$r->UserName."</td>";
           echo " <td>".$r->branch_id."</td>";
           echo " </tr>";
           $i += 1;
        }*/
        echo "</tbody>"; ?>
    </table>
  <?php 
    echo form_open();
    echo form_close();
  ?>
</div> <!-- /container -->
