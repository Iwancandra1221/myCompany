<style> 
table.dataTable td:nth-child(1) {
  width: 20%;
  max-width: 20%;
  word-break: break-all; 
}
</style>
  <script>
    $(document).ready(function() {  
      $('#table1').DataTable({
        "pageLength": 10
      });
      $('#table2').DataTable({
        "pageLength": 10
      });
      //$('#table1').dataTable({searching: false, paging: false, info: false, order: false});
      //$('#table2').dataTable({searching: false, paging: false, info: false, order: false}); 
    } ); 
  </script> 
  <div class="container" style="width:1500px!important;">
    <h3><b>Nama Perusahaan : <?php echo(WEBTITLE); ?></b></h3>
    <h3><b>Bulan : <?php echo(DateTime::createFromFormat('!m', $BULAN)->format('F')); ?>
    <b></h3>
    <h3><b>Tahun : <?php echo($TAHUN);?><b></h3> 
	<a href="ExcelDataGabunganPajakku?th=<?php echo $TAHUN ?>&bl=<?php echo $BULAN ?>" target="_blank" style="float:right"><button>Download Excel</button></a>
    <?php 
      $TOTAL_STAMP = count($ALLBHAKTISTAMP);
      $TOTAL_NOTSTAMP = count($ALLBHAKTINOTSTAMP);
      $TOTAL_METERAI = $TOTAL_STAMP+$TOTAL_NOTSTAMP;
    ?>   

  <div style="padding-top: 30px;">
        <table class="table-bordered w100 border"> 
          <thead class="thead-light">
            <tr>
              <td style="width:300px; text-align:center; background-color:lightyellow;">
                KETERANGAN
              </td> 
              <td style="width:150px; text-align:center; background-color:lightyellow;">
                JUMLAH
              </td> 
           </tr>
          <thead> 
          <tbody>
            <tr>
              <td>
                <h3>Jumlah Keping Material Awal Bulan</h3>
              </td> 
              <td>
              </td>
            </tr>
            <tr>
              <td>
                <h3>Total Permintaan Kuota Ke Pajakku</h3>
              </td> 
              <td>
              </td>
            </tr>
            <tr>
              <td>
                <h3>Total Berhasil Stamping</h3>
              </td> 
              <td style="text-align:center;">
                <?php echo(count($ALLBHAKTISTAMP)) ?> 
              </td>
            </tr>
            <tr>
              <td>
                <h3>Total Gagal Stamping</h3>
              </td> 
              <td style="text-align:center;">
                <?php echo(count($ALLBHAKTINOTSTAMP)) ?> 
              </td>
            </tr>
            <tr>
              <td>
                <h3>Jumlah Keping Material Akhir Bulan</h3>
              </td> 
              <td>
              </td>
            </tr>
          </tbody> 
        </table>
  </div> 

  <div style="height:20px;clear:both;"></div>
  <div >  
        <table id="table1" class="table table-striped table-bordered" cellspacing="0" width="100%">
          <thead class="thead-light">
            <tr>
              <td colspan="11" style=" text-align:center;">
                DETAIL DATA GAGAL STAMPING
              </td>  
            </tr>
            <tr> 
              <td style="text-align:center;">
                Nama Dokumen
              </td> 
              <td style="text-align:center;">
                Status Stamping
              </td> 
              <td style="text-align:center;">
                Pesan Stamping
              </td> 
              <td style="text-align:center;">
                No Dokumen
              </td> 
              <td style="text-align:center;">
                Jenis Dokumen
              </td> 
              <td style="text-align:center;">
                Tgl Dokumen
              </td> 
              <td style="text-align:center;">
                Serial Number
              </td> 
              <td style="text-align:center;">
                Checksum
              </td> 
              <td style="text-align:center;">
                Dibuat oleh
              </td> 
              <td style="text-align:center;">
                Dibuat Tgl
              </td>  
           </tr>
          <thead> 
          <tbody>
            <?php
              $nomorurut = 1;
              foreach ($ALLBHAKTINOTSTAMP as $key => $value) {
                
                if ($value->Settlement_Status==null || $value->Settlement_Status =="")
                {
                  $value->Settlement_Status = "NOTSTAMP";
                }
                
                echo ('<tr> 
                    <td style="text-align:left;">
                      <h3>'.$value->Document_FileName.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->Settlement_Status.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->ErrorMessage.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->Document_No.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->Document_Type.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.date("d-M-Y",strtotime($value->Document_Date)).'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->EMeterai_SN.'</h3>
                    </td>
                    <td style="text-align:left;"> 
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->EMeterai_RequestBy.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.date("d-M-Y",strtotime($value->EMeterai_StampingDate)).'</h3>
                    </td> 
                  </tr>'); 
                $nomorurut++;
              }
            ?>
          </tbody> 
        </table>
  </div>  

  <div style="height:20px;clear:both;"></div> 
  <div>  
        <table id="table2" class="table table-striped table-bordered" cellspacing="0" width="100%">
          <thead class="thead-light">
            <tr>
              <td colspan="11" style=" text-align:center;">
                DETAIL DATA BERHASIL STAMPING
              </td>  
            </tr>
            <tr> 
              <td style="text-align:center;">
                Nama Dokumen
              </td> 
              <td style="text-align:center;">
                Status Stamping
              </td> 
              <td style="text-align:center;">
                Pesan Stamping
              </td> 
              <td style="text-align:center;">
                No Dokumen
              </td> 
              <td style="text-align:center;">
                Jenis Dokumen
              </td> 
              <td style="text-align:center;">
                Tgl Dokumen
              </td> 
              <td style="text-align:center;">
                Serial Number
              </td> 
              <td style="text-align:center;">
                Checksum
              </td> 
              <td style="text-align:center;">
                Dibuat oleh
              </td> 
              <td style="text-align:center;">
                Dibuat Tgl
              </td>  
            </tr>
          <thead> 
          <tbody>
            <?php
              $nomorurut = 1;
              foreach ($ALLBHAKTISTAMP as $key => $value) 
              {
                if ($value->ErrorMessage!=null || $value->ErrorMessage !="")
                {
                  $value->ErrorMessage = "";
                }

                echo ('<tr> 
                    <td style="text-align:left;">
                      <h3>'.$value->Document_FileName.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->Settlement_Status.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->ErrorMessage.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->Document_No.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->Document_Type.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.date("d-M-Y",strtotime($value->Document_Date)).'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->EMeterai_SN.'</h3>
                    </td>
                    <td style="text-align:left;"> 
                    </td>
                    <td style="text-align:left;">
                      <h3>'.$value->EMeterai_RequestBy.'</h3>
                    </td>
                    <td style="text-align:left;">
                      <h3>'.date("d-M-Y",strtotime($value->EMeterai_StampingDate)).'</h3>
                    </td> 
                  </tr>'); 
                $nomorurut++;
              }
            ?>
          </tbody> 
        </table>
  </div>   
  </div>  
 