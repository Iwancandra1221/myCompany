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

		if ($laporan == "claim") {
			echo form_open("ReportBass/ReportClaimBass_Proses", array("target"=>"_blank"));
		}
		else if ($laporan == "po") {
			echo form_open("ReportBass/ReportPOBass_Proses", array("target"=>"_blank"));
		}
		else if ($laporan == "service") {
			echo form_open("ReportBass/ReportServiceBass_Proses", array("target"=>"_blank"));
		}
        

	?>
	<div class="form-container">
		
        <div class="row">
			<div class="col-3">Cabang</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="cabang" id="cabang" onchange="fCabang()">
                    <?php 
						$jum= count($listcabang->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$listcabang->data[$i]->KODE_CABANG."'
                                >".$listcabang->data[$i]->KODE_CABANG." - ".$listcabang->data[$i]->NAMA_CABANG."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3">Bass</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="bass" id="bass" >
                    <option value="">Pilih Data</option>
                    <?php 
						// $jum= count($listbank->data);
						// for($i=0; $i<$jum; $i++){						
						// 	echo "<option value='".$listbass->data[$i]->KODE_BASS."'
                        //         >".$listbass->data[$i]->KODE_BASS." - ".$listbass->data[$i]->NAMA_BASS."</option>";
						// }			  
					?>
				</select>
			</div>
		</div>

        <div class="row">
			<div class="col-3 col-m-3 row-label">Periode</div>
			<div class="col-2 col-m-3 ">

				<input type="text" class="form-control" id="dp1" placeholder="dd/mm/yyyy" name="dp1" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">-- S/D --</div>
			<div class="col-2 col-m-3 ">
				<input type="text" class="form-control" id="dp2" placeholder="dd/mm/yyyy" name="dp2" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" required>

			</div>
		</div>

        
		<div class="row">

		<?php 
			if ($laporan == "claim") { ?>

				<div class="col-3">TAMPILKAN REPORT CLAIM BASS</div>
				<div class="col-8" style="margin-top:-10px " >
					<div class="col-12">
						<input type="radio" name="radstatus" id="r1" value="noclaim" checked> <label>Berdasarkan Nomor Claim</label> 
						<br>
						<input type="radio" name="radstatus" id="r2" value="nonota"> <label>Berdasarkan Nomor Nota</label>
						<br>
						<input type="radio" name="radstatus" id="r3" value="kdbrg"> <label>Berdasarkan Kode Barang, Nomor Nota</label>
						<br>					
					</div>
				</div>

		<?php 
			}
			else if ($laporan == "po") { ?>

				<div class="col-3">TAMPILKAN REPORT PO BASS</div>
				<div class="col-8" style="margin-top:-10px " >
					<div class="col-12">
						<input type="radio" name="radstatus" id="r1" value="summary" checked> <label>PO Summary</label> 
						<br>
						<input type="radio" name="radstatus" id="r2" value="detail"> <label>PO Detail</label>
						<br>											
					</div>
				</div>

		<?php 
			} 
			else if ($laporan == "service") { ?>		

				<div class="col-3">TAMPILKAN REPORT SERVICE BASS</div>
				<div class="col-8" style="margin-top:-10px " >
					<div class="col-12">
						<input type="radio" name="radstatus" id="r1" value="garansi" checked> <label>GARANSI</label> 
						<br>
						<input type="radio" name="radstatus" id="r2" value="nongaransi"> <label>TIDAK GARANSI</label>
						<br>
						<input type="radio" name="radstatus" id="r2" value="semua"> <label>SEMUA</label>
						<br>											
					</div>
				</div>
			
		<?php 
			} ?>


		</div>
		

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />
			<input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>


<script>
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
	} );

    function fCabang(){
		var cabang = document.getElementById("cabang").value;		
		var bass;

		// alert('<?php //echo $this->API_URL."/ReportBass/GetListBass?api=APITES"; ?>&kodecabang='+cabang);
		        
        $.ajax({ 
			type: "POST",			
			url: '<?php echo $this->API_URL."/ReportBass/GetListBass?api=APITES"; ?>&kodecabang='+cabang,
			success: function (dataa) {			
				// alert (dataa);	
				dataa = JSON.parse(dataa); 	
				// alert (dataa.data[2].jenis_barang);	

				for(i = 0; i < dataa.data.length; i++){							
					bass +='<option value="'+dataa.data[i].KODE_BASS+'">'+dataa.data[i].KODE_BASS+' - '+dataa.data[i].NAMA_BASS+'</option>';   
					$("#bass").html(bass);
				}					
			}
		});     
		return false;
	};  

    fCabang();

</script>





