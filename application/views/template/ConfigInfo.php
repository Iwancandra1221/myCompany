<?php 
  $isShowInfo = false;

  // $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  // echo base_url()."<br>";
  // echo($_SERVER['HTTP_HOST']."<br>");
  // echo($_SERVER['REQUEST_URI']."<br>");
  // echo(str_replace(base_url(),'',$actual_link)."<br>");
  // $actual_link = str_replace(base_url(),'',$actual_link);
  // die($actual_link);

  if (isset($_SESSION["role"])) {
    for($i=0;$i<count($_SESSION["role"]);$i++) {
      if ($_SESSION["role"][$i]=="ROLE01" || $_SESSION["role"][$i]=="ROLE02" || $_SESSION["role"][$i]=="ROLE06") {
        $isShowInfo = true;
      }
    }
  }

  if (!isset($configs)) {
    $isShowInfo = false;
  }

  if ($isShowInfo==true) {
    if (count($configs)>0) { 
?>
      <div class="row">
        <div class="col-1 col-m-2">
          <input type = "button" name="btnInfo" value="INFO" onclick="myFunction();" style="color:black;margin-left:20px;"/>
        </div>
        <div class="col-11 col-m-10" id="divInfo" style="display:none;">
          <?php 
            $no = 1;
            echo ("<b><u>CUSTOM CONFIG</u></b><br>");
            foreach ($configs as $c) {
              echo ("Config #".$no."<br>");
              echo ("Config Type : ".$c->ConfigType."<br>");
              echo ("Config Name : ".$c->ConfigName."<br>");
              echo ("Config Group: ".$c->Group."<br>");
              echo ("<br>");
              $no++;
            }
          ?>
        </div>
      </div>

      <script>
      function myFunction() {
        var x = document.getElementById("divInfo");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
      } 
      </script>    
<?php 
    }
  } 
?>  