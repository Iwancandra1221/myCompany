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
        echo form_open("LaporanDepoPenjualanDanRetur/Proses", array("target"=>"_blank"));
	?>
	<div class="form-container">
		
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
			<div class="col-3">Kategori</div>
			<div class="col-8 col-m-8">
				<input type="radio" name="kategori" id="p1" value="P" checked> <label for="p1">PRODUK</label> &nbsp;&nbsp;&nbsp;
				<input type="radio" name="kategori" id="p2" value="S"> <label for="p2"> SPAREPART</label>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Jenis Transaksi</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="jns_trx" id="jns_trx" required>					
                    <option value='J'selected>Jual</option>
                    <option value='R'>Retur</option>  
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Partner Type</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="partner_type" id="partner_type" onchange = "fpartnertype()" required>
					<option value='ALL' selected>ALL</option>
					<?php 
						$jum= count($partnertypes->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$partnertypes->data[$i]."'>".$partnertypes->data[$i]."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Wilayah</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="wilayah" id="wilayah" onchange = "fWilayah()" required>
					<option value='ALL' selected>ALL</option>
					<?php 
						$jum= count($wilayahs->data);
						for($i=0; $i<$jum; $i++){						
							echo "<option value='".$wilayahs->data[$i]->WILAYAH."'>".$wilayahs->data[$i]->WILAYAH."</option>";
						}			  
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3">Dealer</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="dealer" id="dealer" onchange = "fDealer('ALL')" required>
					<option value="ALL" selected>ALL</option>					 
				</select>
			</div>
		</div>       

        <div class="row">
			<div class="col-3">Wilayah Khusus</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="wilayah_khusus" id="wilayah_khusus" required>
					<option value="ALL" selected>ALL</option>					 
				</select>
			</div>
		</div>       

		<div class="row">
			<div class="col-3">Gudang</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="gudang" id="gudang"  required>
					<option value='ALL' selected>ALL</option>
					<?php 
						foreach ($gudangs->data as $key => $v) {
							echo "<option value='".$v->KD_GUDANG."'>".$v->NM_GUDANG. " --- " .$v->KD_GUDANG. "</option>";
						}			  
					?>
				</select>
			</div>
		</div>

		<!-- <div class="row">
			<div class="col-3">Gudang</div>
			<div class="col-8 col-m-8">
				<input type="radio" name="radgudang" id="d1" value="ALL" onclick ="myFunctionGudang('ALL')" checked> <label for="p1">Semua Gudang</label> &nbsp;&nbsp;&nbsp;
			<br>
				<input type="radio" name="radgudang" id="d2" value="Gudang" onclick ="myFunctionGudang('Gudang')"> <label for="p2">Gudang</label>
				
				<input class="input" type="text" name="gudang" id="gudang" style="color:black" onkeyup="myFunction_CariGudang()" >
				<select class="input form-control" type="text" name="namagudang" id="namagudang" style="color:black" disabled ></select>
			</div>
		</div> -->
 
        <div class="row">
			<div class="col-3">Merk</div>
			<div class="col-8 col-m-8 date">
				<select  class="form-control" name="merk" id="merk" required>
					<option value='ALL' selected>ALL</option>
					<?php 		
						$jum= count($merks->data);
						for($i=0; $i<$jum; $i++){
							echo "<option value='".$merks->data[$i]->MERK."'>".$merks->data[$i]->MERK."</option>";
						}								
					?>
				</select>
			</div>
		</div>
		


		<!-- <div class="row">
			<div class="col-3">Opsi</div>
			<div class="col-2 ">
				<input type="radio" name="opsi" id="op1" value="Format Piutang" checked> <label for="p1">Format Piutang</label> &nbsp;&nbsp;&nbsp;
				</div>	
			<div class="col-2 ">
				<input type="radio" name="opsi" id="op2" value="Per Alamat Kirim"> <label for="p2"> Per Alamat Kirim</label>
				</div>	
			<div class="col-2 ">
				<input type="radio" name="opsi" id="op3" value="Potong PDA" checked> <label for="p1">Potong PDA</label> &nbsp;&nbsp;&nbsp;
				</div>	
			<div class="col-2 ">
				<input type="radio" name="opsi" id="op4" value="Potong Posting"> <label for="p2">Potong Posting</label>
			</div>
		</div> -->

		<div class="row">
           	<div class="col-3 col-m-3 row-label">Opsi</div>
        	<div class="row-input">
				<div class="col-6">
					<input type="checkbox" id="chkformatpiutang" name="chkformatpiutang" value="Y" >
					<label for="chkformatpiutang">Format Piutang</label>
				</div>	
				
				<div class="col-6">
					<input type="checkbox" id="chkperalamatkirim" name="chkperalamatkirim" value="Y" >
					<label for="chkperalamatkirim">Per Alamat Kirim</label>
				</div>

				<!-- <div class="col-6" style="margin-top:-20px">
					<input type="checkbox" id="chkpotongpda" name="chkpotongpda" value="Y" >
					<label for="chkpotongpda">Potong PDA</label>
					</div>	 -->
				
				<div class="col-6" style="margin-top:-20px">
					<input type="checkbox" id="chkpotongposting" name="chkpotongposting" value="Y" >
					<label for="chkpotongposting">Potong Posting</label>
				</div>	
			</div>
        </div>

        <div class="row" align="center" style="padding-top:50px;">
			<input type="submit" name="btnExcel" value="EXCEL"/>
		</div>

	</div> 


<script>
	
	// function myFunction_CariDealer() {
    //     var KodeDealer = document.getElementById("dealer").value;
	// 	var NamaDealer = document.getElementById("namadealer").value;
		
	// 	$.ajax({
	// 		type: "POST",
	// 		url: '<?php //echo $this->API_URL."/LaporanDepoPenjualanDanRetur/GetDealer?api=APITES"; ?>&kdplg='+KodeDealer,
	// 		success: function (dataa) {					
	// 			dataa = JSON.parse(dataa);
	// 			NamaDealer = '<option value="'+dataa.data[0].KD_PLG+'">'+dataa.data[0].NM_PLG+'</option>';
	// 			document.getElementById("namadealer").innerHTML=NamaDealer;							
	// 		}
	// 	});     

	// 	$("#wilayah_khusus").html('<option value="ALL">ALL</option>');	
	// 	var wilayah_khusus='<option value="ALL">ALL</option>';
	// 	$.ajax({ 
	// 		type: "POST",
	// 		url: '<?php //echo $this->API_URL."/LaporanDepoPenjualanDanRetur/GetListWilayahKhusus?api=APITES"; ?>&kdplg='+KodeDealer,
	// 		success: function (dataa) {			
	// 			dataa = JSON.parse(dataa); 	 
	// 			for(i = 0; i < dataa.data.length; i++){							
	// 				wilayah_khusus +='<option value="'+dataa.data[i].wilayah+'">'+dataa.data[i].wilayah+'</option>';   
	// 				$("#wilayah_khusus").html(wilayah_khusus);
	// 			}					
	// 		}
	// 	});             
    // }   

	
	// function myFunction_CariGudang() {
    //     var KodeGudang = document.getElementById("gudang").value;
	// 	var NamaGudang = document.getElementById("namagudang").value;
		
	// 	$.ajax({
	// 		type: "POST",
	// 		url: '<?php //echo $this->API_URL."/LaporanDepoPenjualanDanRetur/GetGudang?api=APITES"; ?>&kdgudang='+KodeGudang,
	// 		success: function (dataa) {					
	// 			dataa = JSON.parse(dataa);
	// 			NamaGudang = '<option value="'+dataa.data[0].KD_GUDANG+'">'+dataa.data[0].NM_GUDANG+'</option>';
	// 			document.getElementById("namagudang").innerHTML=NamaGudang;							
	// 		}
	// 	});                
    // }   

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

	// function myFunctionDealer(cb) {
    //     var x = document.getElementById("dealer");
    //     var y = document.getElementById("namadealer");

    //     // jgn lupa break; !!!
    //     switch (cb) {
    //         case "ALL" :
    //             x.disabled = true;
	// 			x.value='';
	// 			y.value='';
    //             // y.disabled = true;
    //             break;
    //     	case "Dealer" :
    //             x.disabled = false;
    //             // y.disabled = false;
    //     }   
    // }
	// myFunctionDealer('ALL');

	// function myFunctionGudang(cb) {
    //     var x = document.getElementById("gudang");
    //     var y = document.getElementById("namagudang");

    //     // jgn lupa break; !!!
    //     switch (cb) {
    //         case "ALL" :
    //             x.disabled = true;
	// 			x.value='';
	// 			y.value='';
    //             // y.disabled = true;
    //             break;
    //     	case "Gudang" :
    //             x.disabled = false;
    //             // y.disabled = false;
    //     }   
    // }
	// myFunctionGudang('ALL');

	function fWilayah(){
		var nmwil = document.getElementById("wilayah").value;
		var dealer='<option value="ALL" selected>ALL</option>';

		$.ajax({
			type: "POST",
			url: '<?php echo $this->API_URL."/MsDealer/GetDealerByNmWil?api=APITES"; ?>&nmwil='+nmwil,
			
			success: function (dataa) {		
				dataa = JSON.parse(dataa); 	 
				for(i = 0; i < dataa.data.length; i++){							
					dealer += '<option value="'+dataa.data[i].KD_PLG+'">'
							+dataa.data[i].NM_PLG+ ' --- ' +dataa.data[i].KD_PLG+'</option>';  
				}		
				$("#dealer").html(dealer);									
			}
		});    
	}

	function fDealer(){
		var KodeDealer = document.getElementById("dealer").value;
		var wilayah_khusus='<option value="ALL" selected>ALL</option>';

		$.ajax({ 
			type: "POST",
			url: '<?php echo $this->API_URL."/MsDealer/GetListWilayahKhusus?api=APITES"; ?>&kdplg='+KodeDealer,

			success: function (dataa) {		
				// alert (dataa)	;

				dataa = JSON.parse(dataa); 	 
				for(i = 0; i < dataa.data.length; i++){							
					wilayah_khusus +='<option value="'+dataa.data[i].KD_WIL+'">'+dataa.data[i].NM_WIL+ ' --- ' +dataa.data[i].KD_WIL+ '</option>';  
				}		
				$("#wilayah_khusus").html(wilayah_khusus);			
			}
		});      
	}


</script>



