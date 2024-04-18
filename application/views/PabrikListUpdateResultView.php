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

<table border="1" width="100%" cellspacing="8" cellpadding="6" style="border-collapse: collapse";>
<tr>
  <th>Tanggal Faktur</th><th>No Faktur </th><th> Kode Barang </th><th> Qty </th><th> Tanggal Kirim </th><th> Nama Ekspedisi </th>
  <th> Nomor Ekspedisi </th><th> Nomor Container </th><th> Container Seal </th><th> Sopir </th><th> No Plat Mobil</th>
</tr>
<?php
$cust = "";
foreach($data as $content) {
  if($cust != $content->Nama){
?>
  <tr>
    <td colspan="12"><b><?php echo $content->Nama;?></b></td>
  </tr>
<?php
    $cust = $content->Nama;
  }
?>
  <tr>
    <td><?php echo date('d M Y', strtotime($content->Tgl_Faktur));?></td>
    <td><?php echo $content->No_Faktur;?></td>
    <td><?php echo $content->Kd_Brg;?></td>
    <td><?php echo number_format($content->Qty,0);?></td>
    <?php 
      if(is_null($content->Tgl_Kirim)){
        echo "<td>&nbsp;</td>";
      }
      else{
      ?>
    <td><?php echo date('d M Y', strtotime($content->Tgl_Kirim));?></td>
    <?php } ?>
    <td><?php echo $content->ExpName;?></td>
    <td><?php echo $content->ExpNO;?></td>
    <td><?php echo $content->ContainerNO;?></td>
    <td><?php echo $content->ContainerSeal;?></td>
    <td><?php echo $content->Sopir;?></td>
    <td><?php echo $content->NoPlatMobil;?></td>
  </tr>
<?php   
  }
?>
</table>
