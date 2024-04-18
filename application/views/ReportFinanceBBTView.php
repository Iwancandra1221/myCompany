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
		$jum = 0;
        echo form_open("ReportFinance/ReportFinanceBBT_Proses", array("target"=>"_blank",'id'=>'f-report'));
	?>
	<div class="form-container">
		
        <div class="row">
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="wilayah" id="wilayah" >
                    <option value="**  |  ALL  |  SEMUA WILAYAH" selected>ALL</option>		
					<?php 

						if($dbwilayah!=null){
							$jum= count($dbwilayah['data']);
							for($i=0; $i<$jum; $i++){						
								echo "<option value='".$dbwilayah['data'][$i]['WILAYAH']."'
	                                >".$dbwilayah['data'][$i]['WILAYAH']."</option>";
							}	
						}
								  
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
			<div class="col-3">Tipe Trans</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="tipetrans" id="tipetrans" >
                    <option value="ALL">ALL</option>		
					<option value="BBT" selected>BBT (BBTP + BBTL + BBTS)</option>	

					<?php 
						if($tipetrans!=null){
							$jum= count($tipetrans['data']);
							for($i=0; $i<$jum; $i++){						
								echo "<option value='".$tipetrans['data'][$i]['TYPE_TRANS']."'
	                                >".$tipetrans['data'][$i]['TYPE_TRANS']."</option>";
							}	
						}
								  
					?>
				</select>
			</div>
		</div>


		<div class="row">
			<div class="col-3">Tipe Terima</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="tipe_terima" id="tipe_terima">
					<option value='ALL' selected>ALL</option>
          <option value='CASH' >CASH</option>
          <option value='CHECK' >CHECK</option>
					<option value='GIRO' >GIRO</option>
          <option value='TRANSFER' >TRANSFER</option>	
					<option value='TRANSFER VA' >TRANSFER VA</option>	
					<option value='QRIS-S' >QRIS-S</option>	
					<option value='QRIS-D' >QRIS-D</option>	
					<option value='EDC' >EDC</option>					
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Status</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="status" id="status" >
					<option value='ALL' selected>ALL</option>
                    <option value='CAIR' >CAIR</option>
                    <option value='MUNDUR' >MUNDUR</option>
                    <option value='BOOKING' >BOOKING</option>					
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Rekening</div>
			<div class="col-8" style="margin-top:-10px " >
				<div class="col-12">
					<input type="radio" name="radrekening" id="r1" value="gabungan" onclick="frekening('gabungan')" checked> <label> Gabungan</label> 
					<br>
					<input type="radio" name="radrekening" id="r2" value="gruprekening" onclick="frekening('gruprekening')"> <label> Grup No Rekening</label>
					<br>
					<input type="radio" name="radrekening" id="r3" value="norekening" onclick="frekening('norekening')"> <label> No Rekening</label>
					<br>
					<select  class="form-control" name="rekening" id="rekening" style="width:100%; float:left" disabled>
					<?php 
						if($dbaccountbank!=null){
							$jum= count($dbaccountbank['data']);
							for($i=0; $i<$jum; $i++){						
								echo "<option value='".$dbaccountbank['data'][$i]['REKENING']."'
	                                >".$dbaccountbank['data'][$i]['REKENING']."</option>";
							}	
						}
								  
					?>
					</select>
				</div>
			</div>
		</div>
		
		<div class="row">
		<div class="col-3"> </div>
			<div class="col-8">
				<input type="checkbox" id="cetak_detail" name="cetak_detail" value="Y" >
				<label for="cetak_detail">Cetak Detail</label>
			</div>	
		</div>	

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnPreview" id="btnPreview" value="PREVIEW" />
			<input type="submit" name="btnExcel" id="btnExcel" value="EXCEL"/>
		</div>
	</div>

	<?php echo form_close(); ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

		// $("#btnPreview").click(function(){
		// 	var formData = new FormData($("#f-report")[0]);
		// 	formData.append("btnPreview","PREVIEW");
		// 	var url =  $("#f-report")[0].action;

		// 	$.ajax({
	    //         type: "POST",               // Metode HTTP
	    //         url: url,               // URL tujuan
	    //         data: formData,             // Objek FormData
	    //         processData: false,         // Tidak memproses data secara otomatis
	    //         contentType: false,         // Tidak mengatur jenis konten secara otomatis
	    //         dataType: "json",
	    //         success: function(resp) {
	    //             // Penanganan respons sukses dari server
	    //             console.log(resp);
	    //         },
	    //         error: function(error) {
	    //             // Penanganan kesalahan dalam permintaan AJAX
	    //             console.error("Error:", error);
	    //         }
        // 	});
		// });
	});


	function frekening(rek) {
        var x = document.getElementById("rekening");

		if (rek == "norekening"){
			x.disabled = false ;
		}  
		else {
			x.disabled = true;
		}
		
		return false;      
    }   

</script>



