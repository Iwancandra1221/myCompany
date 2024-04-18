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
        echo form_open("Reportfakturgantung/Reportfakturgantung_Proses", array("target"=>"_blank"));		
	?>

	<div class="form-container">
		        
        <div class="row">
			<div class="col-3">Partner Type</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="partnertype" id="partnertype" onchange="fWilayah()">
                    <!-- <option value='all' selected>ALL</option> -->

                    <?php 
						$jum= count($listpartnertype->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listpartnertype->data[$i]->PARTNER_TYPE."'
                                >".$listpartnertype->data[$i]->PARTNER_TYPE."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="wilayah" id="wilayah" onchange="fWilayah()">
                    <option value='all' selected>ALL</option>

                    <?php 
						$jum= count($listwilayah->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listwilayah->data[$i]->WILAYAH."'
                                >".$listwilayah->data[$i]->WILAYAH."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Salesman</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="salesman" id="salesman" >
                    <option value='all|all' selected>ALL</option>

                    <?php 
						$jum= count($listsalesman->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listsalesman->data[$i]->KD_SLSMAN."|".$listsalesman->data[$i]->NM_SLSMAN."'
                                >".$listsalesman->data[$i]->NM_SLSMAN."</option>";
						}			  
					?>
				</select>
			</div>
		</div>


        <div class="row">
			<div class="col-3">Divisi</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="divisi" id="divisi">
                    <option value='all' selected>ALL</option>

                    <?php 
						$jum= count($listdivisi->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listdivisi->data[$i]->DIVISI."'
                                >".$listdivisi->data[$i]->DIVISI."</option>";
						}			  
					?>
				</select>
			</div>
		</div>
        
        <div class="row">
			<div class="col-3">Toko</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="toko" id="toko" >
                    <option value="">Pilih Data</option>
                    
				</select>
			</div>
		</div>
        

        <div class="row">
			<div class="col-3">Status</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="status" id="status" >
                    <option value="all">ALL</option>
                    <option value="sudah">Sudah Jatuh Tempo</option>
                    <option value="belum">Belum Jatuh Tempo</option>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Jumlah Hari Telat Min</div>
			<div class="col-8 col-m-8 ">
                <input type="text" name="hari" id="hari" value="1" style="text-align:center;" required>
			</div>
		</div>

        <!-- <div class="row">
			<div class="col-3">Pilihan Report</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="report" id="report">
                    <option value='detail'>Detail</option>
                    <option value='summary'>Summary</option>
                    <option value='sisatoko'>Sisa Per Toko</option>
                    <option value='sisawil'>Sisa Per Wilayah</option>					
				</select>
			</div>
		</div> -->
        		

        <div class="row" align="center" style="padding-top:50px;">		
            <input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />	
			<input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>


<script>
    
    document.getElementById("hari").addEventListener("input", function() {
        // Menggunakan event listener untuk menghapus nilai jika bukan angka atau lebih kecil dari 1
        var angkaInput = document.getElementById("hari");
        var angkaValue = angkaInput.value;
        if (!(/^\d+$/.test(angkaValue))) {
            angkaInput.value = 1;
        } else if (angkaValue < 1) {
            angkaInput.value = 1;
        }
    });

	function fWilayah(){
		var wilayah = document.getElementById("wilayah").value;	
        var partnertype = document.getElementById("partnertype").value;		
		var toko;

		// alert('<?php //echo $this->API_URL."/MsDealer/GetListDealers?api=APITES"; ?>&partnertype='+partnertype+'&wilayah='+wilayah);
		        
        $.ajax({ 
			type: "POST",			
			url: '<?php echo $this->API_URL."/MsDealer/GetListDealers?api=APITES"; ?>&partner_type='+partnertype+'&wilayah='+wilayah,
			success: function (dataa) {			
				// alert (dataa);	
				dataa = JSON.parse(dataa); 	
				// alert (dataa.data[2].jenis_barang);	

                toko +='<option value="all|all">ALL</option>';   
				$("#toko").html(toko);

				for(i = 0; i < dataa.data.length; i++){							
					toko +='<option value="'+dataa.data[i].nama+'">'+dataa.data[i].nama+'</option>';   
					$("#toko").html(toko);
				}					
			}
		});     
		return false;
	};  

    fWilayah();


</script>





