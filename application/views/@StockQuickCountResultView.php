<style type="text/css">
    body { 
    	color: black;
    }

    .form-container {
      width: 400px;
      height: 250px;
      /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#deefff+0,98bede+100;Blue+3D+%2310 */
      background: #deefff; /* Old browsers */
      background: -moz-linear-gradient(top,  #deefff 0%, #98bede 100%); /* FF3.6-15 */
      background: -webkit-linear-gradient(top,  #deefff 0%,#98bede 100%); /* Chrome10-25,Safari5.1-6 */
      background: linear-gradient(to bottom,  #deefff 0%,#98bede 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
      filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#deefff', endColorstr='#98bede',GradientType=0 ); /* IE6-9 */

      border:1px solid blue;
      border-radius:15px;
      padding:15px;
    }

    .row {
      line-height:30px; 
      vertical-align:middle;
      clear:both;
    }
    .row-label, .row-input {
      float:left;
    }
    .row-label {
      width:40%;
    }
    .row-input {
      width:60%;
    }

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

    table thead {
      /*position: fixed;*/
    }
</style>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Stock Quick Count Preview</title>

  <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>
</head>
<body>
<button id='btnExport'>Export to Excel</button>
<?php

  $content_html = "<div id='dvData' style='width:100%;'>";

  $content_html.= "<div id='header' style='width:100%;'>";
  $content_html.= " <div><h2>STOCK QUICK COUNT</h2></div>";
  $content_html.= " <div><h3>TANGGAL ".date("d-M-Y", strtotime($tgl))."</h3></div>";
  // $content_html.= "<div><h3>DIVISI : ".$divisi."</h3></div>";
  $content_html.= "</div>";
  $content_html.= "<table border=1 style='border-collapse:collapse; border: medium solid; border-color: #282531;' cellspacing='15' cellpadding='7'>";

  $content_html.= "<thead>";

  $content_html.= "<tr>";
  $content_html.= "<td align='center'>Kode Barang</td>";

  // cetak wilayah
  for($i=0;$i<count($wilayah);$i++){
    $content_html.= "<td  align='center'  bgcolor='#40e0d0'>".$wilayah[$i]->wilayah."</td>";
  }

  // total paling kanan (per-kd_brg)
  $content_html.= "<td align='center' bgcolor='#f00c93' ><b>TOTAL</b></td>";
  $content_html.= "</tr>";

  $content_html.= "</thead>";
  // cetak data

  $grandtotal = 0;
  $listtotalperjenis = array(array());
 
  $count = 0;
  $listtotalperjenis[$count] = array();
  
  $totalperwil = 0;
  $grandtotalperkdbrg = 0;

  $listtotal = array();
  $listgrandtotal = array();
  for($i=0;$i<(count($wilayah) + 1);$i++){
    array_push($listgrandtotal, 0);
  }
  $content_html.= "<tr>";
  for($i=0;$i<count($barang);$i++){
    $grandtotalperkdbrg = 0;

    if($i==0 or $barang[$i]->Jns_Brg != $barang[$i-1]->Jns_Brg){

      if(($i>0 and $barang[$i]->Jns_Brg != $barang[$i-1]->Jns_Brg)){

        // total per jenis barang dicetak sebelum jenis barang yang baru, jenis barang paling akhir ada d bawah (sblm cetak grand total)  
        $content_html.= "<tr bgcolor='#997379'>";
        $content_html.= "<td><b>TOTAL</b></td>";
        
        $listtotal = array();
        for($j=0;$j<$count;$j++){
          for($k=0;$k<count($listtotalperjenis[$j]);$k++){
            if($j==0){
              array_push($listtotal, $listtotalperjenis[$j][$k]);
              $listgrandtotal[$k] += $listtotalperjenis[$j][$k];
            }
            else{
              $listtotal[$k] += $listtotalperjenis[$j][$k];
              $listgrandtotal[$k] += $listtotalperjenis[$j][$k];
            }
          }
         
        }

        for($j=0;$j<count($listtotal);$j++){
          $content_html.= "<td bgcolor='#997379'><b>".round($listtotal[$j],3)."</b></td>";
        }

        
        $count = 0;
        $listtotalperjenis[$count] = array();
        $content_html.= "</tr>";
      }

      $content_html.= "<tr>";
      $content_html.= "<td colspan=".(count($wilayah)+2)." bgcolor='#ffb3b3'><b>".$barang[$i]->Jns_Brg."</b></td>";
      
      $content_html.= "</tr>";
    }

    $content_html.= "<tr>";
    $content_html.= "<td>".$barang[$i]->kd_brg."</td>";

    for($j=0;$j<count($wilayah);$j++){
      $totalperwil = 0;
      $ada = 0;
      for($l=0;$l<count($data);$l++){
        if($data[$l]->kd_brg == $barang[$i]->kd_brg and rtrim($data[$l]->wilayah) == rtrim($wilayah[$j]->wilayah)){
          
          $content_html.= "<td>".$data[$l]->Qty."</td>";
          $totalperwil += $data[$l]->Qty;
          $grandtotalperkdbrg += $data[$l]->Qty;
          array_push($listtotalperjenis[$count], $data[$l]->Qty);
            
          $ada = 1;
          break;
        }
      }

      if($ada == 0){
        $content_html.= "<td>0</td>";
        array_push($listtotalperjenis[$count], 0);
      }

      // // total per wilayah
      // $content_html.= "<td bgcolor='##87a485'><b>".$totalperwil."</b></td>";
      // array_push($listtotalperjenis[$count], $totalperwil);

      // // rata'' per wilayah
      // $content_html.= "<td bgcolor='#ccff00'><b>".round($totalperwil/count($bulan),3)."</b></td>";
      // array_push($listtotalperjenis[$count], round($totalperwil/count($bulan),3));
    }

    // grand total & rata2 per kd brg
    $content_html.= "<td bgcolor='#f00c93'><b>".$grandtotalperkdbrg."</b></td>";
    array_push($listtotalperjenis[$count], $grandtotalperkdbrg);

    $count += 1;
    $listtotalperjenis[$count] = array();

    $content_html.= "</tr>";

    // total per jenis barang paling akhir dan grand total
    if($i == (count($barang) - 1)){
        $content_html.= "<tr bgcolor='#997379'>";
        $content_html.= "<td><b>TOTAL</b></td>";
        
        $listtotal = array();
        for($j=0;$j<$count;$j++){
          for($k=0;$k<count($listtotalperjenis[$j]);$k++){
            if($j==0){
              array_push($listtotal, $listtotalperjenis[$j][$k]);
              $listgrandtotal[$k] += $listtotalperjenis[$j][$k];
            }
            else{
              $listtotal[$k] += $listtotalperjenis[$j][$k];
              $listgrandtotal[$k] += $listtotalperjenis[$j][$k];
            }
          }
         
        }

        for($j=0;$j<count($listtotal);$j++){
          $content_html.= "<td><b>".round($listtotal[$j],3)."</b></td>";
        }

        
        $count = 0;
        $listtotalperjenis[$count] = array();
        $content_html.= "</tr>";

        // grand total
        $content_html.= "<tr bgcolor='#ffa7b6'>";
        $content_html.= "<td><b>GRANDTOTAL</b></td>";
        
        // total per jenis barang
        for($j=0;$j<count($listgrandtotal);$j++){
          $content_html.= "<td><b>".round($listgrandtotal[$j],3)."</b></td>";
        }
        $content_html.= "</tr>";
    }

  }
  
  $content_html.= "</table>";


  echo $content_html;

  function getNamaBulan($bulan){
    switch($bulan){
      case '01':
        return 'JAN';
      case '02':
        return 'FEB';
      case '03':
        return 'MAR';
      case '04':
        return 'APR';
      case '05':
        return 'MEI';
      case '06':
        return 'JUN';
      case '07':
        return 'JUL';
      case '08':
        return 'AGU';
      case '09':
        return 'SEP';
      case '10':
        return 'OKT';
      case '11':
        return 'NOV';
      case '12':
        return 'DES';
    }
  }
?>
<script>
  $(document).ready(function() {
    $("#btnExport").click(function(e) {
        //getting values of current time for generating the file name
        var dt = new Date();
        var day = dt.getDate();
        var month = dt.getMonth() + 1;
        var year = dt.getFullYear();
        var hour = dt.getHours();
        var mins = dt.getMinutes();
        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
        //creating a temporary HTML link element (they support setting file names)
        var a = document.createElement('a');
        //getting data from our div that contains the HTML table
        var data_type = 'data:application/vnd.ms-excel';
        var table_div = document.getElementById('dvData');
        var table_html = table_div.outerHTML.replace(/ /g, '%20');
        a.href = data_type + ', ' + table_html;
        //setting the file name
        a.download = 'StockQuickCount_' + postfix + '.xls';
        //triggering the function
        a.click();
        //just in case, prevent default behaviour
        e.preventDefault();
    });
  });
</script>

</body>
</html>