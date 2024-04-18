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
   /*     width: 150px;
        height: 150px;*/
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
<html lang="en" xml:lang="en">
<head>
  <title>Laporan Penjualan Qty Result</title>

  <script src="<?php echo base_url('dist/js/jquery.min.js');?>"></script>
</head>
<body>
<button id='btnExport'>Export to Excel</button>
<?php

  $content_html = "<div id='dvData' style='width:100%;'>";

  $content_html.= "<div id='header' style='width:100%;'>";
  $content_html.= " <div><h2>LAPORAN PENJUALAN QUANTITY</h2></div>";
  $content_html.= " <div><h3>TANGGAL ".date("d-M-Y", strtotime($tglawal))." s/d ".date("d-M-Y", strtotime($tglakhir))."</h3></div>";
  $content_html.= " <div><h3>DIVISI : ".$divisi."</h3></div>";
  $content_html.= "</div>";

  $content_html.= "<table border=1 style='border-collapse:collapse; border: medium solid; border-color: #282531;' cellspacing='15' cellpadding='7'>";

  $content_html.= "<thead>";

  $content_html.= "<tr>";
  $content_html.= "<td rowspan=2 align='center'>Kode Barang</td>";

  // cetak wilayah
  for($i=0;$i<count($wilayah);$i++){
    $content_html.= "<td colspan=".((count($bulan)+2) * 2 + count($bulan))." align='center'  bgcolor='#40e0d0'>".$wilayah[$i]->wilayah."</td>";
  }
  $content_html.= "<td colspan=".((count($bulan)+2) * 2 + count($bulan))." align='center'  bgcolor='#40e0d0'>GRANDTOTAL</td>";

  //grand total paling kanan (per-kd_brg)
  $content_html.= "<td align='center' bgcolor='#1eda6f' rowspan='2'><b>GRANDTOTAL ".$tahun."</b></td>";
  $content_html.= "<td align='center' bgcolor='#fac776' rowspan='2'><b>RATA'' ".$tahun."</b></td>";

  $content_html.= "<td align='center' bgcolor='#1eda6f' rowspan='2'><b>GRANDTOTAL ".(int)($tahun-1)."</b></td>";
  $content_html.= "<td align='center' bgcolor='#fac776' rowspan='2'><b>RATA'' ".(int)($tahun-1)."</b></td>";

  $content_html.= "<td align='center' bgcolor='#f00c93' rowspan='2'><b>GROWTH</b></td>";
  $content_html.= "</tr>";

  // cetak bulan
  $content_html.= "<tr>";
  for($i=0;$i<(count($wilayah)+1);$i++){
      for($j=0;$j<count($bulan);$j++){
        $content_html.= "<td align='center'>".getNamaBulan($bulan[$j])." ".$tahun."</td>";
      }

      $content_html.= "<td align='center' bgcolor='#1eda6f'><b>Total ".$tahun."</b></td>";
      $content_html.= "<td align='center' bgcolor='#fac776'><b>Rata'' ".$tahun."</b></td>";

      for($j=0;$j<count($bulan);$j++){
        $content_html.= "<td align='center'>".getNamaBulan($bulan[$j])." ".(int)($tahun-1)."</td>";
      }
      $content_html.= "<td align='center' bgcolor='#1eda6f'><b>Total ".(int)($tahun-1)."</b></td>";
      $content_html.= "<td align='center' bgcolor='#fac776'><b>Rata'' ".(int)($tahun-1)."</b></td>";

      for($j=0;$j<count($bulan);$j++){
        $content_html.= "<td align='center' bgcolor='#f00c93'><b>Growth ".getNamaBulan($bulan[$j])."</b></td>";
      }
  }
  $content_html.= "</tr>";
  $content_html.= "</thead>";

  // cetak data

  $grandtotal = 0;
  $listtotalperjenis = array(array());
 
  $count = 0;
  $listtotalperjenis[$count] = array();
  
  $totalperwil = 0;
  $grandtotalperkdbrg = 0;
  $grandtotalperkdbrgts = 0;

  $listtotal = array();
  $listgrandtotal = array();
  for($i=0;$i<((count($wilayah)+1)*(((count($bulan)+2)*2)+count($bulan))+5);$i++){
    array_push($listgrandtotal, 0);
  }

  $listdatagt = array(0,0,0,0,0,0,0,0,0,0,0,0);
  $listdatagtsblmnya  = array(0,0,0,0,0,0,0,0,0,0,0,0);

  $content_html.= "<tr>";
  for($i=0;$i<count($barang);$i++){
    $grandtotalperkdbrg = 0;
    $grandtotalperkdbrgts = 0;
    if($i==0 or $barang[$i]->Jns_Brg != $barang[$i-1]->Jns_Brg){
      // total per jenis barang dicetak sebelum jenis barang yang baru, jenis barang paling akhir ada d bawah (sblm cetak grand total)
      if($i>0){
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
        $temp_gt = $listtotal[count($listtotal)-4];
        $temp_gtts = $listtotal[count($listtotal)-2];
        if($temp_gtts == 0)
          $content_html.= "<td bgcolor='#997379'><b>0</b></td>";
        else
          $content_html.= "<td bgcolor='#997379'><b>".round(($temp_gt-$temp_gtts)/$temp_gtts*100,3)."</b></td>";
        
        $count = 0;
        $listtotalperjenis[$count] = array();
        $content_html.= "</tr>";
      }

      // kolom jenis barang
      $content_html.= "<tr>";
      $content_html.= "<td colspan=".((((count($bulan)+2)*2)+count($bulan))*(count($wilayah)+1)+6)." bgcolor='#ffb3b3'><b>".$barang[$i]->Jns_Brg."</b></td>";
      
      $content_html.= "</tr>";
    }

    $content_html.= "<tr>";
    $content_html.= "<td>".$barang[$i]->Kd_Brg."</td>";

    

    for($j=0;$j<count($wilayah);$j++){
      $totalperwil = 0;
      
      for($k=0;$k<count($bulan);$k++){
        $ada = 0;
        for($l=0;$l<count($data);$l++){
          if($data[$l]->Kd_Brg == $barang[$i]->Kd_Brg and $data[$l]->Wilayah == $wilayah[$j]->wilayah){
            
            switch($bulan[$k]){
              case '01':
                $content_html.= "<td>".$data[$l]->BL01A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL01A;
                array_push($listtotalperjenis[$count], $data[$l]->BL01A);
                $listdatagt[0] += $data[$l]->BL01A;
                break;
              case '02':
                $content_html.= "<td>".$data[$l]->BL02A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL02A;
                array_push($listtotalperjenis[$count], $data[$l]->BL02A);
                $listdatagt[1] += $data[$l]->BL02A;
                break;
              case '03':
                $content_html.= "<td>".$data[$l]->BL03A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL03A;
                array_push($listtotalperjenis[$count], $data[$l]->BL03A);
                $listdatagt[2] += $data[$l]->BL03A;
                break;
              case '04':
                $content_html.= "<td>".$data[$l]->BL04A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL04A;
                array_push($listtotalperjenis[$count], $data[$l]->BL04A);
                $listdatagt[3] += $data[$l]->BL04A;
                break;
              case '05':
                $content_html.= "<td>".$data[$l]->BL05A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL05A;
                array_push($listtotalperjenis[$count], $data[$l]->BL05A);
                $listdatagt[4] += $data[$l]->BL05A;
                break;
              case '06':
                $content_html.= "<td>".$data[$l]->BL06A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL06A;
                array_push($listtotalperjenis[$count], $data[$l]->BL06A);
                $listdatagt[5] += $data[$l]->BL06A;
                break;
              case '07':
                $content_html.= "<td>".$data[$l]->BL07A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL07A;
                array_push($listtotalperjenis[$count], $data[$l]->BL07A);
                $listdatagt[6] += $data[$l]->BL07A;
                break;
              case '08':
                $content_html.= "<td>".$data[$l]->BL08A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL08A;
                array_push($listtotalperjenis[$count], $data[$l]->BL08A);
                $listdatagt[7] += $data[$l]->BL08A;
                break;
              case '09':
                $content_html.= "<td>".$data[$l]->BL09A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL09A;
                array_push($listtotalperjenis[$count], $data[$l]->BL09A);
                $listdatagt[8] += $data[$l]->BL09A;
                break;
              case '10':
                $content_html.= "<td>".$data[$l]->BL10A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL10A;
                array_push($listtotalperjenis[$count], $data[$l]->BL10A);
                $listdatagt[9] += $data[$l]->BL10A;
                break;
              case '11':
                $content_html.= "<td>".$data[$l]->BL11A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL11A;
                array_push($listtotalperjenis[$count], $data[$l]->BL11A);
                $listdatagt[10] += $data[$l]->BL11A;
                break;
              case '12':
                $content_html.= "<td>".$data[$l]->BL12A."</td>";
                $grandtotalperkdbrg += $data[$l]->BL12A;
                array_push($listtotalperjenis[$count], $data[$l]->BL12A);
                $listdatagt[11] += $data[$l]->BL12A;
                break;
            }
            
            $totalperwil = $data[$l]->TOTALA;


            $ada = 1;
            break;
          }
        }

        if($ada == 0){
          $content_html.= "<td>0</td>";
          array_push($listtotalperjenis[$count], 0);
        }

      }

      // total per wilayah
      $content_html.= "<td bgcolor='#1eda6f'><b>".$totalperwil."</b></td>";
      array_push($listtotalperjenis[$count], $totalperwil);
      // rata'' per wilayah
      $content_html.= "<td bgcolor='#fac776'><b>".round($totalperwil/count($bulan),3)."</b></td>";
      array_push($listtotalperjenis[$count], round($totalperwil/count($bulan),3));

      // tahun sebelumnya
      $totalperwil = 0;
      $listgrowth = array();

      for($k=0;$k<count($bulan);$k++){
        $ada = 0;
        for($l=0;$l<count($data);$l++){
          if($data[$l]->Kd_Brg == $barang[$i]->Kd_Brg and $data[$l]->Wilayah == $wilayah[$j]->wilayah){
            
            switch($bulan[$k]){
              case '01':
                $content_html.= "<td>".$data[$l]->BL01B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL01B;
                array_push($listtotalperjenis[$count], $data[$l]->BL01B);
                array_push($listgrowth, $data[$l]->GROWTH01);
                $listdatagtsblmnya[0] += $data[$l]->BL01B;
                break;
              case '02':
                $content_html.= "<td>".$data[$l]->BL02B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL02B;
                array_push($listtotalperjenis[$count], $data[$l]->BL02B);
                array_push($listgrowth, $data[$l]->GROWTH02);
                $listdatagtsblmnya[1] += $data[$l]->BL02B;
                break;
              case '03':
                $content_html.= "<td>".$data[$l]->BL03B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL03B;
                array_push($listtotalperjenis[$count], $data[$l]->BL03B);
                array_push($listgrowth, $data[$l]->GROWTH03);
                $listdatagtsblmnya[2] += $data[$l]->BL03B;
                break;
              case '04':
                $content_html.= "<td>".$data[$l]->BL04B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL04B;
                array_push($listtotalperjenis[$count], $data[$l]->BL04B);
                array_push($listgrowth, $data[$l]->GROWTH04);
                $listdatagtsblmnya[3] += $data[$l]->BL04B;
                break;
              case '05':
                $content_html.= "<td>".$data[$l]->BL05B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL05B;
                array_push($listtotalperjenis[$count], $data[$l]->BL05B);
                array_push($listgrowth, $data[$l]->GROWTH05);
                $listdatagtsblmnya[4] += $data[$l]->BL05B;
                break;
              case '06':
                $content_html.= "<td>".$data[$l]->BL06B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL06B;
                array_push($listtotalperjenis[$count], $data[$l]->BL06B);
                array_push($listgrowth, $data[$l]->GROWTH06);
                $listdatagtsblmnya[5] += $data[$l]->BL06B;
                break;
              case '07':
                $content_html.= "<td>".$data[$l]->BL07B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL07B;
                array_push($listtotalperjenis[$count], $data[$l]->BL07B);
                array_push($listgrowth, $data[$l]->GROWTH07);
                $listdatagtsblmnya[6] += $data[$l]->BL07B;
                break;
              case '08':
                $content_html.= "<td>".$data[$l]->BL08B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL08B;
                array_push($listtotalperjenis[$count], $data[$l]->BL08B);
                array_push($listgrowth, $data[$l]->GROWTH08);
                $listdatagtsblmnya[7] += $data[$l]->BL08B;
                break;
              case '09':
                $content_html.= "<td>".$data[$l]->BL09B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL09B;
                array_push($listtotalperjenis[$count], $data[$l]->BL09B);
                array_push($listgrowth, $data[$l]->GROWTH09);
                $listdatagtsblmnya[8] += $data[$l]->BL09B;
                break;
              case '10':
                $content_html.= "<td>".$data[$l]->BL10B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL10B;
                array_push($listtotalperjenis[$count], $data[$l]->BL10B);
                array_push($listgrowth, $data[$l]->GROWTH10);
                $listdatagtsblmnya[9] += $data[$l]->BL10B;
                break;
              case '11':
                $content_html.= "<td>".$data[$l]->BL11B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL11B;
                array_push($listtotalperjenis[$count], $data[$l]->BL11B);
                array_push($listgrowth, $data[$l]->GROWTH11);
                $listdatagtsblmnya[10] += $data[$l]->BL11B;
                break;
              case '12':
                $content_html.= "<td>".$data[$l]->BL12B."</td>";
                $grandtotalperkdbrgts += $data[$l]->BL12B;
                array_push($listtotalperjenis[$count], $data[$l]->BL12B);
                array_push($listgrowth, $data[$l]->GROWTH12);
                $listdatagtsblmnya[11] += $data[$l]->BL12B;
                break;
            }
            
            $totalperwil = $data[$l]->TOTALB;

            $ada = 1;
            break;
          }
        }

        if($ada == 0){
          $content_html.= "<td>0</td>";
          array_push($listtotalperjenis[$count], 0);
          array_push($listgrowth, 0);
        }
      }

      // total per wilayah tahun sebelumnya
      $content_html.= "<td bgcolor='#1eda6f'><b>".$totalperwil."</b></td>";
      array_push($listtotalperjenis[$count], $totalperwil);
      // rata'' per wilayah tahun sebelumnya
      $content_html.= "<td bgcolor='#fac776'><b>".round($totalperwil/count($bulan),3)."</b></td>";
      array_push($listtotalperjenis[$count], round($totalperwil/count($bulan),3));
      
      //growth
      for($m=0;$m<count($listgrowth);$m++){
        $content_html.= "<td bgcolor='#f00c93'><b>".$listgrowth[$m]."</b></td>";
        array_push($listtotalperjenis[$count], $listgrowth[$m]);
      }
    }

    // grand total seluruh wilayah
    $totalperwil = 0;

    for($k=0;$k<count($bulan);$k++){
      switch($bulan[$k]){
        case '01':
          $content_html.= "<td>".$listdatagt[0]."</td>";
          $totalperwil += $listdatagt[0];
          array_push($listtotalperjenis[$count], $listdatagt[0]);
          break;
        case '02':
          $content_html.= "<td>".$listdatagt[1]."</td>";
          $totalperwil += $listdatagt[1];
          array_push($listtotalperjenis[$count], $listdatagt[1]);
          break;
        case '03':
          $content_html.= "<td>".$listdatagt[2]."</td>";
          $totalperwil += $listdatagt[2];
          array_push($listtotalperjenis[$count], $listdatagt[2]);
          break;
        case '04':
          $content_html.= "<td>".$listdatagt[3]."</td>";
          $totalperwil += $listdatagt[3];
          array_push($listtotalperjenis[$count], $listdatagt[3]);
          break;
        case '05':
          $content_html.= "<td>".$listdatagt[4]."</td>";
          $totalperwil += $listdatagt[4];
          array_push($listtotalperjenis[$count], $listdatagt[4]);
          break;
        case '06':
          $content_html.= "<td>".$listdatagt[5]."</td>";
          $totalperwil += $listdatagt[5];
          array_push($listtotalperjenis[$count], $listdatagt[5]);
          break;
        case '07':
          $content_html.= "<td>".$listdatagt[6]."</td>";
          $totalperwil += $listdatagt[6];
          array_push($listtotalperjenis[$count], $listdatagt[6]);
          break;
        case '08':
          $content_html.= "<td>".$listdatagt[7]."</td>";
          $totalperwil += $listdatagt[7];
          array_push($listtotalperjenis[$count], $listdatagt[7]);
          break;
        case '09':
          $content_html.= "<td>".$listdatagt[8]."</td>";
          $totalperwil += $listdatagt[8];
          array_push($listtotalperjenis[$count], $listdatagt[8]);
          break;
        case '10':
          $content_html.= "<td>".$listdatagt[9]."</td>";
          $totalperwil += $listdatagt[9];
          array_push($listtotalperjenis[$count], $listdatagt[9]);
          break;
        case '11':
          $content_html.= "<td>".$listdatagt[10]."</td>";
          $totalperwil += $listdatagt[10];
          array_push($listtotalperjenis[$count], $listdatagt[10]);
          break;
        case '12':
          $content_html.= "<td>".$listdatagt[11]."</td>";
          $totalperwil += $listdatagt[11];
          array_push($listtotalperjenis[$count], $listdatagt[11]);
          break;
      }
    }
    $content_html.= "<td bgcolor='#1eda6f'><b>".$totalperwil."</b></td>";
    array_push($listtotalperjenis[$count], $totalperwil);
    $content_html.= "<td bgcolor='#fac776'><b>".round($totalperwil/count($bulan),3)."</b></td>";
    array_push($listtotalperjenis[$count], round($totalperwil/count($bulan),3));

    // grand total seluruh wilayah thn sblmnya
    $totalperwil = 0;
    $listgrowth = array();

    for($k=0;$k<count($bulan);$k++){
      switch($bulan[$k]){
        case '01':
          $content_html.= "<td>".$listdatagtsblmnya[0]."</td>";
          $totalperwil += $listdatagtsblmnya[0];
          $growth = 0;
          if($listdatagt[0] == $listdatagtsblmnya[0])
            $growth = 0;
          else{
            if($listdatagtsblmnya[0] == 0){
              if($listdatagt[0] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[0]-$listdatagtsblmnya[0])*100)/$listdatagtsblmnya[0],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[0]);
          break;
        case '02':
          $content_html.= "<td>".$listdatagtsblmnya[1]."</td>";
          $totalperwil += $listdatagtsblmnya[1];
          $growth = 0;
          if($listdatagt[1] == $listdatagtsblmnya[1])
            $growth = 0;
          else{
            if($listdatagtsblmnya[1] == 0){
              if($listdatagt[1] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[1]-$listdatagtsblmnya[1])*100)/$listdatagtsblmnya[1],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[1]);
          break;
        case '03':
          $content_html.= "<td>".$listdatagtsblmnya[2]."</td>";
          $totalperwil += $listdatagtsblmnya[2];
          $growth = 0;
          if($listdatagt[2] == $listdatagtsblmnya[2])
            $growth = 0;
          else{
            if($listdatagtsblmnya[2] == 0){
              if($listdatagt[2] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[2]-$listdatagtsblmnya[2])*100)/$listdatagtsblmnya[2],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[2]);
          break;
        case '04':
          $content_html.= "<td>".$listdatagtsblmnya[3]."</td>";
          $totalperwil += $listdatagtsblmnya[3];
          $growth = 0;
          if($listdatagt[3] == $listdatagtsblmnya[3])
            $growth = 0;
          else{
            if($listdatagtsblmnya[3] == 0){
              if($listdatagt[3] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[3]-$listdatagtsblmnya[3])*100)/$listdatagtsblmnya[3],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[3]);
          break;
        case '05':
          $content_html.= "<td>".$listdatagtsblmnya[4]."</td>";
          $totalperwil += $listdatagtsblmnya[4];
          $growth = 0;
          if($listdatagt[4] == $listdatagtsblmnya[4])
            $growth = 0;
          else{
            if($listdatagtsblmnya[4] == 0){
              if($listdatagt[4] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[4]-$listdatagtsblmnya[4])*100)/$listdatagtsblmnya[4],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[4]);
          break;
        case '06':
          $content_html.= "<td>".$listdatagtsblmnya[5]."</td>";
          $totalperwil += $listdatagtsblmnya[5];
          $growth = 0;
          if($listdatagt[5] == $listdatagtsblmnya[5])
            $growth = 0;
          else{
            if($listdatagtsblmnya[5] == 0){
              if($listdatagt[5] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[5]-$listdatagtsblmnya[5])*100)/$listdatagtsblmnya[5],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[5]);
          break;
        case '07':
          $content_html.= "<td>".$listdatagtsblmnya[6]."</td>";
          $totalperwil += $listdatagtsblmnya[6];
          $growth = 0;
          if($listdatagt[6] == $listdatagtsblmnya[6])
            $growth = 0;
          else{
            if($listdatagtsblmnya[6] == 0){
              if($listdatagt[6] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[6]-$listdatagtsblmnya[6])*100)/$listdatagtsblmnya[6],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[6]);
          break;
        case '08':
          $content_html.= "<td>".$listdatagtsblmnya[7]."</td>";
          $totalperwil += $listdatagtsblmnya[7];
          $growth = 0;
          if($listdatagt[7] == $listdatagtsblmnya[7])
            $growth = 0;
          else{
            if($listdatagtsblmnya[7] == 0){
              if($listdatagt[7] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[7]-$listdatagtsblmnya[7])*100)/$listdatagtsblmnya[7],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[7]);
          break;
        case '09':
          $content_html.= "<td>".$listdatagtsblmnya[8]."</td>";
          $totalperwil += $listdatagtsblmnya[8];
          $growth = 0;
          if($listdatagt[8] == $listdatagtsblmnya[8])
            $growth = 0;
          else{
            if($listdatagtsblmnya[8] == 0){
              if($listdatagt[8] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[8]-$listdatagtsblmnya[8])*100)/$listdatagtsblmnya[8],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[8]);
          break;
        case '10':
          $content_html.= "<td>".$listdatagtsblmnya[9]."</td>";
          $totalperwil += $listdatagtsblmnya[9];
          $growth = 0;
          if($listdatagt[9] == $listdatagtsblmnya[9])
            $growth = 0;
          else{
            if($listdatagtsblmnya[9] == 0){
              if($listdatagt[9] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[9]-$listdatagtsblmnya[9])*100)/$listdatagtsblmnya[9],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[9]);
          break;
        case '11':
          $content_html.= "<td>".$listdatagtsblmnya[10]."</td>";
          $totalperwil += $listdatagtsblmnya[10];
          $growth = 0;
          if($listdatagt[10] == $listdatagtsblmnya[10])
            $growth = 0;
          else{
            if($listdatagtsblmnya[10] == 0){
              if($listdatagt[10] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[10]-$listdatagtsblmnya[10])*100)/$listdatagtsblmnya[10],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[10]);
          break;
        case '12':
          $content_html.= "<td>".$listdatagtsblmnya[11]."</td>";
          $totalperwil += $listdatagtsblmnya[11];
          $growth = 0;
          if($listdatagt[11] == $listdatagtsblmnya[11])
            $growth = 0;
          else{
            if($listdatagtsblmnya[11] == 0){
              if($listdatagt[11] > 0)
                $growth = 100;
              else
                $growth = -100;
            }
            else
              $growth = round((($listdatagt[11]-$listdatagtsblmnya[11])*100)/$listdatagtsblmnya[11],2);
          }
          array_push($listgrowth, $growth);
          array_push($listtotalperjenis[$count], $listdatagtsblmnya[11]);
          break;
      }
    }
    $content_html.= "<td bgcolor='#1eda6f'><b>".$totalperwil."</b></td>";
    array_push($listtotalperjenis[$count], $totalperwil);
    $content_html.= "<td bgcolor='#fac776'><b>".round($totalperwil/count($bulan),3)."</b></td>";
    array_push($listtotalperjenis[$count], round($totalperwil/count($bulan),3));

    //growth
    for($m=0;$m<count($listgrowth);$m++){
      $content_html.= "<td bgcolor='#f00c93'><b>".round($listgrowth[$m],3)."</b></td>";
      array_push($listtotalperjenis[$count], $listgrowth[$m]);
    }

    // grand total & rata2 per kd brg
    $content_html.= "<td bgcolor='#1eda6f'><b>".$grandtotalperkdbrg."</b></td>";
    array_push($listtotalperjenis[$count], $grandtotalperkdbrg);

    $content_html.= "<td bgcolor='#fac776'><b>".round(($grandtotalperkdbrg/(count($bulan))),3)."</b></td>";
    array_push($listtotalperjenis[$count], ($grandtotalperkdbrg/(count($bulan))));

    //--------------------------------------------------------------------------------------------------------
    // grand total & rata2 per kd brg tahun sebelumnya
    $content_html.= "<td bgcolor='#1eda6f'><b>".$grandtotalperkdbrgts."</b></td>";
    array_push($listtotalperjenis[$count], $grandtotalperkdbrgts);

    $content_html.= "<td bgcolor='#fac776'><b>".round(($grandtotalperkdbrgts/(count($bulan))),3)."</b></td>";
    array_push($listtotalperjenis[$count], ($grandtotalperkdbrgts/(count($bulan))));

    //--------------------------------------------------------------------------------------------------------
    //growth
    if($grandtotalperkdbrgts == 0)
      $content_html.= "<td bgcolor='#f00c93'><b>0</b></td>";
    else
      $content_html.= "<td bgcolor='#f00c93'><b>".round(($grandtotalperkdbrg-$grandtotalperkdbrgts)/$grandtotalperkdbrgts*100,3)."</b></td>";
    // array_push($listtotalperjenis[$count], round(($grandtotalperkdbrg-$grandtotalperkdbrgts)/$grandtotalperkdbrgts*100,3));

    $count += 1;
    $listtotalperjenis[$count] = array();

    $content_html.= "</tr>";

    // total per jenis barang paling akhir dan grand total
    if($i == (count($barang) - 1)){
       
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

        $temp_gt = $listtotal[count($listtotal)-4];
        $temp_gtts = $listtotal[count($listtotal)-2];
        if($temp_gtts == 0)
          $content_html.= "<td bgcolor='#997379'><b>100</b></td>";
        else
          $content_html.= "<td bgcolor='#997379'><b>".round(($temp_gt-$temp_gtts)/$temp_gtts*100,3)."</b></td>";
        
        $count = 0;
        $listtotalperjenis[$count] = array();
        $content_html.= "</tr>";


        // grand total
        $content_html.= "<tr bgcolor='#ffa7b6'>";
        $content_html.= "<td><b>GRANDTOTAL</b></td>";
        
        // total per jenis barang
        for($j=0;$j<count($listgrandtotal);$j++){  
          if($j == (count($listgrandtotal)-1)){
            $temp_gt = $listgrandtotal[count($listgrandtotal)-4];
            $temp_gtts = $listgrandtotal[count($listgrandtotal)-2];

            if($temp_gtts == 0)
              $content_html.= "<td><b>100</b></td>";
            else
              $content_html.= "<td><b>".round(($temp_gt-$temp_gtts)/$temp_gtts*100,3)."</b></td>";
            
            $content_html.= "</tr>";
          }
          else{
            $content_html.= "<td><b>".round($listgrandtotal[$j],3)."</b></td>";
          }
        }
    }

  }
  
  $content_html.= "</table>";
  $content_html.= "</div>";
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
<script src="<?php echo base_url('dist/js/FileSaver.js');?>"></script>
<script src="<?php echo base_url('dist/js/FileSaver.min.js');?>"></script>

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
        
        var data = new Blob([document.getElementById('dvData').innerHTML], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
        });
        saveAs(data, "LaporanPenjualanQty_"+ postfix +".xls");
    });
  });

  
</script>

</body>
</html>