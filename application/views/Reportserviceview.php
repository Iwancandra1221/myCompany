<script>
    var DealerSelected = "<?php echo($DealerSelected)?>";

    //Form Load
    $(document).ready(function() {

        $("#modal_barang").hide();
        $('#kodebarang').prop('readonly',false);
        $('#namabarang').prop('readonly',true);

		$('#dp11').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp11').datepicker('getDate');
			$('#dp12').datepicker("setStartDate", StartDt);
		});
		
		$('#dp12').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp12').datepicker('getDate');
			$('#dp11').datepicker("setEndDate", EndDt);
		});

		$('#dp21').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp21').datepicker('getDate');
			$('#dp22').datepicker("setStartDate", StartDt);
		});
		
		$('#dp22').datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp22').datepicker('getDate');
			$('#dp21').datepicker("setEndDate", EndDt);
		});

        $("#kodebarang").change(function(){   
			var kodebarang = $('#kodebarang').val();
			$.post("<?php echo site_url('reportservice/carinamabarang'); ?>", {
                kodebarang:kodebarang
				}, function(data){
					console.log(data);
                    $('#namabarang').val(data);
			});   
        });

        $(".select2").select2({
            
        });
        $("#report").change(function(){
            var value = $(this).val();
            if(value=='01'){
                $("#tanggal1").css({"pointer-events":"none"}).val("TANGGAL-SERVICE");
                $("#tanggal2").css({"pointer-events":"none"}).val('TANGGAL-KOSONG');
            }
            else{
                $("#tanggal1").css({"pointer-events":"auto"})
                $("#tanggal2").css({"pointer-events":"auto"});
            }
        });

        $("#report").change(function(){
            if ($("#report").val()!="09" && $("#report").val()!="05") {
                alert ("Selain Report 05, 06, 07 dan 09 Laporan Summary Service Harian, yang lain masih dalam pengerjaan!");
            } 
        });

	} ); //close $(document).ready(function() {

    //Function
    function Browse(div){
        if(div==1){
            $('#kodebarang').val('');
            $('#namabarang').val('');
            $('#modal_barang').modal('show'); 

            // var merk = $('#merk').val();
            // $.ajax({
	        //     url: "<?php echo site_url('reportservice/listbarangsesuaidivisi'); ?>",
            //     type: 'post',
            //     data: {merk: merk},
            //     success: function(response){ 
            //         // Add response in Modal body
            //         //$('.modal-body').html(response);
            //         //$('#modal_barang').html(response);
            //         $barangs = response;
            //         $('.modal-body').html(response);

            //         // Display Modal
            //         $('#modal_barang').modal('show'); 
            //     }
            // });

        }
    }

	function PilihBarang(kodebarang, namabarang){
		$('#kodebarang').val(kodebarang);
        $('#namabarang').val(namabarang);
		$('#modal_barang').modal('hide');
	}

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
        echo form_open("reportservice/proses_report", array("target"=>"_blank"));
	?>
	<div class="form-container">
	
        <form method='POST'>

            <div class="row">
                <div class="col-3">Nama Laporan</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="report" id="report" required>
                        <option value='01'>01 Laporan Harian Service tanpa Detail Sparepart</option>
                        <option value='02'>02 Laporan Harian Service dengan Detail Sparepart</option>
                        <option value='03'>03 Laporan Ongkos Service</option>
                        <option value='04'>04 Laporan Service berdasarkan Teknisi</option>
                        <option value='05'>05 Laporan Produk Masuk Service dari Pembeli/Distributor</option>
                        <option value='06'>06 Laporan Service Pajak</option>
                        <option value='07'>07 Laporan Service Per Kode Barang By QTY</option>
                        <option value='08'>08 Laporan Pemasukan Service</option>
                        <option value='09' selected>09 Laporan Summary Service Harian</option> 
                        <option value='10'>10 Laporan Summary Service Harian Group By Metode Bayar</option>
                    </select>
                 </div>
            </div>

            <div class="row">
                <div class="col-3">Kode Nota Service</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="kodenotaservice" id="kodenotaservice" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($kodenotaservices);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$kodenotaservices[$i]->Kode_Nota."'>".$kodenotaservices[$i]->Kode_Nota."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3 col-m-3 row-label">Tanggal (wajib)</div>
                <div class="col-8 col-m-8 date" style="width:250px;">
                    <select class="form-control" name="tanggal1" id="tanggal1" required>
                        <option value='TANGGAL-KEMBALI' selected>TANGGAL KEMBALI</option> 
                        <option value='TANGGAL-SELESAI'>TANGGAL SELESAI</option> 
                        <option value='TANGGAL-SERVICE'>TANGGAL SERVICE</option> 
                        <option value='TANGGAL-TRANSAKSI'>TANGGAL TRANSAKSI</option> 
                        <option value='TANGGAL-PAJAK'>TANGGAL PAJAK</option> 
                    </select>
                </div>
                <div class="col-8 col-m-8 date" style="width:150px;">
                    <input type="text" class="form-control" id="dp11" placeholder="dd-mm-yyyy" name="dp11" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
                <div class="col-1 col-m-1" style="width:50px;">SD</div>
                <div class="col-8 col-m-8 date" style="width:150px;">
                    <input type="text" class="form-control" id="dp12" placeholder="dd-mm-yyyy" name="dp12" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
            </div>

            <div class="row">
                <div class="col-3 col-m-3 row-label">Tanggal (optional)</div>
                <div class="col-8 col-m-8 date" style="width:250px;">
                    <select class="form-control" name="tanggal2" id="tanggal2" required>
                        <option value='TANGGAL-KOSONG' selected></option>
                        <option value='TANGGAL-KEMBALI'>TANGGAL KEMBALI</option> 
                        <option value='TANGGAL-SELESAI'>TANGGAL SELESAI</option> 
                        <option value='TANGGAL-SERVICE'>TANGGAL SERVICE</option> 
                        <option value='TANGGAL-TRANSAKSI'>TANGGAL TRANSAKSI</option> 
                        <option value='TANGGAL-PAJAK'>TANGGAL PAJAK</option> 
                    </select>
                </div>
                <div class="col-8 col-m-8 date" style="width:150px;">
                    <input type="text" class="form-control" id="dp21" placeholder="dd-mm-yyyy" name="dp21" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
                <div class="col-1 col-m-1" style="width:50px;">SD</div>
                <div class="col-8 col-m-8 date" style="width:150px;">
                    <input type="text" class="form-control" id="dp22" placeholder="dd-mm-yyyy" name="dp22" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Merk</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="merk" id="merk" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($merks);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$merks[$i]->Merk."'>".$merks[$i]->Merk."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Barang</div>
                <div class="col-3 col-m-3 date" style="width:250px;">
                    <input type="text" class="form-control" id="kodebarang" placeholder="" name="kodebarang" value="ALL" autocomplete="off" required>
                </div>
                <div class="col-3 col-m-3 date" style="width:350px;">
                    <input type="text" class="form-control" id="namabarang" placeholder="" name="namabarang" value="ALL" autocomplete="off" required>
                </div>     
                <div class="col-3 col-m-3 date" style="width:150px;">  
                    <a href="javascript:Browse('1');" class="btn btn-info btn-sm btnSearchBarang" data=""><span class="fa fa-edit"></span> Search Barang </a> 
                </div>          
            </div>

            <div class="row">
                <div class="col-3">Teknisi</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="teknisi" id="teknisi" required>
                        <option value='ALL' selected>ALL</option> 
                        <?php 
                            $jum= count($teknisis);
                            for($i=0; $i<$jum; $i++){		
                                echo "<option value='".$teknisis[$i]->Kd_Teknisi."'>".$teknisis[$i]->Kd_Teknisi." - ".$teknisis[$i]->Nm_Teknisi."</option>";			
                            }	
                        ?>
                    </select>
                </div>
            </div>
    
            <div class="row">
                <div class="col-3">Garansi</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="garansi" id="garansi" required>
                        <option value='ALL' selected>ALL</option> 
                        <option value='GARANSI'>GARANSI</option> 
                        <option value='TIDAK-GARANSI'>TIDAK GARANSI</option> 
                    </select>
                 </div>
            </div>

            <div class="row">
                <div class="col-3">Metode Bayar</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control select2" name="metodebayar[]" id="metodebayar[]" multiple="multiple" required>
                        <option value='ALL' selected>ALL</option> 
                        <option value='CASH'>CASH</option> 
                        <option value='EDC'>EDC</option> 
                        <option value='GIRO'>GIRO</option> 
                        <option value='TRANSFER'>TRANSFER</option> 
                        <option value='QRIS'>QRIS</option> 
                        <option value='QRIS-D'>QRIS-D</option> 
                        <option value='QRIS-S'>QRIS-S</option> 
                    </select>
                 </div>
            </div>

            <div class="row">
                <div class="col-3">Cetak</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="cetak" id="cetak" required>
                        <option value='01' selected>01 CETAK SEMUA</option>
                        <option value='02'>02 BELUM Selesai</option>
                        <option value='03'>03 SUDAH Selesai tapi BELUM diambil kembali</option>
                        <option value='04'>04 BELUM SELESAI dan SUDAH Selesai tapi BELUM diambil kembali</option>
                        <option value='05'>05 SUDAH Selesai dan SUDAH diambil Konsumen</option>
                    </select>
                 </div>
            </div>

            <div class="row">
                <div class="col-3">Cetak Per Merk</div>
                <div class="col-8 col-m-8 date">
                    <label> <input type="checkbox" name="cetakpermerk" id="cetakpermerk" value="cetakpermerk"> </label>
                </div>
            </div>

            <div class="row">
                <div class="col-3">Status</div>
                <div class="col-8 col-m-8 date">
                    <select class="form-control" name="status" id="status" required>
                        <option value='ALL'>ALL</option>
                        <option value='BATAL'>BATAL</option>
                        <option value='TIDAK-BATAL' selected>TIDAK BATAL</option>
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

<!-- Modal -->
<div class="modal fade" id="modal_barang" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Pilih Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="table_dealer">
                    <thead>
                        <tr>
                            <th width="50px">No</th>
                            <th width="300px">Kode Barang</th>
                            <th width="500px">Nama Barang</th>
                            <th width="150px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no = 0;
                            foreach($barangs as $barang){
                                $no++;
                            ?>
                            <tr>
                                <td width="50px"><?php echo $no ?></td>
                                <td width="300px"><?php echo $barang->KD_BRG ?></td>
                                <td width="500px"><?php echo $barang->NM_BRG ?></td>
                                <td width="150px" align="center"><a href="javascript:PilihBarang('<?php echo $barang->KD_BRG ?>','<?php echo $barang->NM_BRG ?>');" class="btn btn-info btn-sm btnPilihBarang" data=""><span class="fa fa-edit"></span> Pilih Barang </a></td> 
                             </tr>                   
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

