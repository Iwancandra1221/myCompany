<script>
    $(document).ready(function() {
      $('#TblReqApproval').DataTable({
        "pageLength": 25
      });
    });
</script>


<style>
    #btn_prosses {
        color : white;
        background-color : blue;
        width : 100%;
    }

    #btn_back {
        color : white;
        background-color : blue;
        width : 10%;
        text-align : right;
    }
</style>


<div class="container">
    <div>
        <a href="#" onclick="history.go(-1)"> <button type="button" id="btn_back">Back</button></a>
        <br><br>
    </div>

    <table id="TblReqApproval" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th width="25%">Request</th>
                <th width="40%">Dealer</th>
                <th width="25%">CL Direquest</th>
                <th width="10%">Action</th>
            </tr>
        </thead>

        <tbody id="TblReqApprovalBody">
            <?php
                
                foreach($ReqApproval as $value) {
                    $RequestNo = $value->RequestNo;
                    $RequestDate = $value->RequestDate;
                    $RequestByName = $value->RequestByName;
                    
                    $NamaDealer=$value->AddInfo6Value;
                    $KodeDealer=$value->AddInfo1Value;
                    $Wilayah=$value->AddInfo9Value;

                    $Divisi = $value->AddInfo2Value;
                    $CLAwal = $value->AddInfo7Value;
                    $CLAkhir = $value->AddInfo3Value;
                    $Kenaikan = $CLAkhir-$CLAwal;
                    
                    $urlencode_norequest= urlencode($RequestNo);

                    $url_proses=site_url("MsDealerApproval/ProcessRequest?type=CL&id=".$urlencode_norequest);

                    echo "<tr>"; 
                    echo "<td>".$RequestNo."<br>".$RequestDate."<br>".$RequestByName."</td>";
                    echo "<td>".$NamaDealer."<br>".$KodeDealer."<br>".$Wilayah."</td>"; 
                    echo "<td>Divisi: ".$Divisi."<br>CL Awal: ".number_format($CLAwal)."<br>CL Baru: ".number_format($CLAkhir)."<br>Kenaikan: ".number_format($Kenaikan)."</td>";

                    // echo '<td><a href="'.site_url("MsDealer/ProcessRequest?type=CL&id=".$urlencode_norequest).'">
                    //         <button type="button" id="btn_prosses">Proses</button></a></td>';
                    echo '<td><a href="'.$url_proses.'">
                            <button type="button" id="btn_prosses">Proses</button></a></td>';

                    echo "</tr>";

                }
            ?>
        </tbody>        
    </table>

    <div>
        <a href="#" onclick="history.go(-1)"> <button type="button" id="btn_back">Back</button></a>
    </div>
</div>