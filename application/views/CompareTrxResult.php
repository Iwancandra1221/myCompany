<script>
  $(document).ready(function() {
    /*$('#dp1').datepicker({
      format: "mm/dd/yyyy",
      autoclose: true
    });

    $('#dp2').datepicker({
      format: "mm/dd/yyyy",
      autoclose: true
    });*/

  });
</script>

<style type="text/css">
  th, td { border:1px solid #ccc; padding:3px; font-size:9pt; }

</style>

<div class="container" style="left:0px;width:1360px;overflow-x:scroll;height:500px;overflow-y:scroll;">
  <?php
      //$data["trxType"] = $trxType;
      //$data["trxJkt"] = $jTrx;
      //$data["trxCbg"] = $cTrx;
      //$data["dp1"] = $dp1;
      //$data["dp2"] = $dp2;
  ?>
  <?php if($trxType=="FAKTUR") { ?>
  <table>
    <tr>
      <th rowspan='2' width='4%'>No</th>
      <th colspan='4'>JKT</th>
      <th colspan='4'>CBG</th>
    </tr>
    <tr>
      <th width="10%">NoFaktur</th>
      <th width="10%">TglFaktur</th>
      <th width="18%">Pelanggan</th>
      <th width="10%">TotalFaktur</th>
      <th width="10%">NoFaktur</th>
      <th width="10%">TglFaktur</th>
      <th width="18%">Pelanggan</th>
      <th width="10%">TotalFaktur</th>
    </tr>
    <?php 
      $jTrxCount = count($trxJkt);
      $cTrxCount = count($trxCbg);
      $j = 0;
      $c = 0;
      $MaxCount = (($jTrxCount>$cTrxCount)? $jTrxCount:$cTrxCount);
      $brs = "";
      $NO = 0;
      $maroon = "color:#a31515;font-weight:bold;";
      $black = "color:#0b0f8a;";

      for($i=0;$i<$MaxCount;$i++) {
        $NO += 1;

        if ($trxJkt[$j]["NOFAKTUR"]==$trxCbg[$c]["NOFAKTUR"]) {
          if (date("d-M-Y",strtotime($trxJkt[$j]["TGLFAKTUR"]))!=date("d-M-Y",strtotime($trxCbg[$j]["TGLFAKTUR"]))) {
            $warna = $maroon;
          } else if ($trxJkt[$j]["KDPLG"]!=$trxCbg[$c]["KDPLG"]) {
            $warna = $maroon;
          } else if ($trxJkt[$j]["GRANDTOTAL"]!=$trxCbg[$c]["GRANDTOTAL"]) {
            $warna = $maroon;
          } else {
            $warna = $black;
          }
          $brs.= "<tr style='".$warna."'>";
          $brs.= "  <td>".$NO."</td>";
          $brs.= "  <td>".$trxJkt[$j]["NOFAKTUR"]."</td>";
          $brs.= "  <td>".date("d-M-Y",strtotime($trxJkt[$j]["TGLFAKTUR"]))."</td>";
          $brs.= "  <td>".$trxJkt[$j]["NMPLG"]."<br>".$trxJkt[$j]["KDPLG"]."</td>";
          $brs.= "  <td>".number_format($trxJkt[$j]["GRANDTOTAL"])."</td>";
          $brs.= "  <td>".$trxCbg[$c]["NOFAKTUR"]."</td>";
          $brs.= "  <td>".date("d-M-Y",strtotime($trxCbg[$c]["TGLFAKTUR"]))."</td>";
          $brs.= "  <td>".$trxCbg[$c]["NMPLG"]."<br>".$trxCbg[$c]["KDPLG"]."</td>";
          $brs.= "  <td>".number_format($trxCbg[$c]["GRANDTOTAL"])."</td>";
          $brs.= "</tr>";
          $j+=1;
          $c+=1;
        } else if ($trxJkt[$j]["NOFAKTUR"]<$trxCbg[$c]["NOFAKTUR"]) {
          $brs.= "<tr style='".$maroon."'>";
          $brs.= "  <td>".$NO."</td>";
          $brs.= "  <td>".$trxJkt[$j]["NOFAKTUR"]."</td>";
          $brs.= "  <td>".date("d-M-Y", strtotime($trxJkt[$j]["TGLFAKTUR"]))."</td>";
          $brs.= "  <td>".$trxJkt[$j]["NMPLG"]."<br>".$trxJkt[$j]["KDPLG"]."</td>";
          $brs.= "  <td>".number_format($trxJkt[$j]["GRANDTOTAL"])."</td>";
          $brs.= "  <td></td>";
          $brs.= "  <td></td>";
          $brs.= "  <td></td>";
          $brs.= "  <td></td>";
          $brs.= "</tr>";
          $j+=1;          
        } else {
          $brs.= "<tr style='".$maroon."'>";
          $brs.= "  <td>".$NO."</td>";
          $brs.= "  <td></td>";
          $brs.= "  <td></td>";
          $brs.= "  <td></td>";
          $brs.= "  <td></td>";
          $brs.= "  <td>".$trxCbg[$c]["NOFAKTUR"]."</td>";
          $brs.= "  <td>".date("d-M-Y",strtotime($trxCbg[$c]["TGLFAKTUR"]))."</td>";
          $brs.= "  <td>".$trxCbg[$c]["NMPLG"]."<br>".$trxCbg[$c]["KDPLG"]."</td>";
          $brs.= "  <td>".number_format($trxCbg[$c]["GRANDTOTAL"])."</td>";
          $brs.= "</tr>";
          $c+=1;
        }
      }

      echo($brs);
    ?>
    </table>;
    <?php } ?>
</div> 