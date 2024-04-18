<script>
    var DealerSelected = "<?php echo($DealerSelected)?>";
	
    $(document).ready(function() {
		$('#dp1').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		});
		
		$('#dp2').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});

        // Kondisi CheckBox saat Form di-load
        if (!$('#filterberdasarkankodedealer').is(':checked')) {
            // $('#dealer').attr('disabled','disabled'); 
            // $('#wilayahkhusus').attr('disabled','disabled'); 
            $('#dealer').val('ALL');
            $('#wilayahkhusus').val('ALL');
        } else {
            // $('#dealer').removeAttr('disabled');
            // $('#wilayahkhusus').removeAttr('disabled');
            $('#dealer').focus();
            $('#wilayahkhusus').focus();
        }

        // Kondisi saat CheckBox diklik
        $('#filterberdasarkankodedealer').click(function() {
            if (!$(this).is(':checked')) {
                // $('#dealer').attr('disabled','disabled'); 
                // $('#wilayahkhusus').attr('disabled','disabled'); 
                $('#dealer').val('ALL');
                $('#wilayahkhusus').val('ALL');
            } else {
                // $('#dealer').removeAttr('disabled');
                // $('#wilayahkhusus').removeAttr('disabled');
                $('#dealer').focus();
                $('#wilayahkhusus').focus();
            }
        });

	} );

</script>

<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	/* .row-label {
    padding-left: 15px;
    width:180px;
	} */
	.row-input {
    width:420px;
	}
</style>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open("Reportjualreturexcludeppn/LaporanJualReturExcludePPN", array("target"=>"_blank"));
	?>
	<div class="form-container">
	
        <form method='POST'>

            <div class="row">
                <div class="col-3 col-m-3 row-label">Tanggal </div>
                <div class="col-3 col-m-3 date">
                    <input type="text" class="form-control" id="dp1" placeholder="dd-mm-yyyy" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
                <div class="col-1 col-m-1">SD</div>
                <div class="col-3 col-m-3 date">
                    <input type="text" class="form-control" id="dp2" placeholder="dd-mm-yyyy" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Partner Type</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="partnertype" id="partnertype" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($partnertype);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$partnertype[$i]."'>".$partnertype[$i]."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Wilayah</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="wilayah" id="wilayah" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($wilayah);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$wilayah[$i]->WILAYAH."'>".$wilayah[$i]->WILAYAH."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Parent Div</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="parentdiv" id="parentdiv" required>
                        <?php 
                            foreach ($parentdiv['data'] as $key => $v) {
                                 echo "<option value='".$v['PARENTDIV']."'>".$v['PARENTDIV']."</option>";       
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Divisi</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="divisi" id="divisi" required>
                        <?php 
                            $jum= count($divisi);
                            for($i=0; $i<$jum; $i++){			
                                echo "<option value='".$divisi[$i]->Kd_Divisi."'>".$divisi[$i]->Kd_Divisi."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Tipe Faktur</div>
                <div class="col-8 col-m-8 date">
                    <select  class="form-control" name="tipefaktur" id="tipefaktur" required>
                        <?php 
                            $jum= count($tipefaktur);
                            for($i=0; $i<$jum; $i++){			
                                echo "<option value='".$tipefaktur[$i]->Tipe_Faktur."'>".$tipefaktur[$i]->Tipe_Faktur."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Kategori Barang</div>
                <div class="col-8 col-m-8 date">
                    <label> <input type="radio" name="kategoribarang" value="P" checked="checked"> Product</label>
                    <br>
                    <label> <input type="radio" name="kategoribarang" value="S"> Sparepart</label>
                    <br>
                    <label> <input type="radio" name="kategoribarang" value="ALL"> Product & Sparepart</label>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Custom Filter Laporan</div>
                <div class="col-8 col-m-8 date">
                    <label> <input type="checkbox" name="filterberdasarkankodedealer" id="filterberdasarkankodedealer" value="filterberdasarkankodedealer"> Filter Berdasarkan Kode Dealer</label>
                    <br>
                    <div style="border: 5px solid #aaa;">
                        <label style="padding-left:50px;"> Pilih Dealer </label>
                        <select  class="form-control" name="dealer" id="dealer" required>
                            <option value='ALL' selected>ALL</option> 
                            <?php 
                                $jum= count($dealer);
                                for($i=0; $i<$jum; $i++){			
                                    echo "<option value='".$dealer[$i]->KD_PLG."'>".$dealer[$i]->NM_PLG." - ".$dealer[$i]->KD_PLG."</option>";			
                                }	
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Nama Laporan</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="report" id="report" required>
                        <option value='JENISBARANG' selected>Laporan Jual Retur Per Jenis Barang</option> 
                        <option value='KODEBARANG'>Laporan Jual Retur Per Kode Barang</option>
                    </select>
                 </div>
            </div>

            <div class="row" align="center" style="padding-top:50px;">
                <input type="submit" name="btnHTML" value="PREVIEW"/>
                <input type="submit" name="btnPDF" value="PDF"/>
                <input type="submit" name="btnExcel" value="EXCEL"/>           
            </div>
        </form>   
    </div>    
    <?php echo form_close(); ?>             
</div> 


