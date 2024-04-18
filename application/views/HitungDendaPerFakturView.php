<style type="text/css">
	.row {
    line-height:30px; 
    vertical-align:middle;
    clear:both;
	}
	.row-label, .row-input {
    float:left;
	}
	.row-label {
    padding-left: 15px;
    width:180px;
	}
	.row-input {
    width:420px;
	}
</style>



<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        // echo form_open("HitungDendaPerFaktur/", array("target"=>"_blank",'method'=>'post'));
	?>
	<div class="form-container">
		<div class="row">
			<div class="col-3">Salesman</div>
			<div class="col-7 col-m-8">
				<select  class="form-control" name="Kd_Slsman" id="Kd_Slsman" required onchange="nonaktif_excel()">
					<option value=""></option>
					<?php 
						if($salesman!=null){
							foreach($salesman as $value){
								echo "<option value='".$value['KD_SLSMAN']."' data-id='".$value['NM_SLSMAN']."' >".$value['NM_SLSMAN']." (".$value['KD_SLSMAN'].")</option>";
							}	
						}  
					?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-3">Periode</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="dp1" placeholder="mm/yyyy" name="periodeawal" autocomplete="off" required onchange="nonaktif_excel()">
			</div>
			<div class="col-1 text-center">
				s/d
			</div>
			<div class="col-3 col-m-3">
				<input type="text" class="form-control" id="dp2" placeholder="mm/yyyy" name="periodeakhir" autocomplete="off" required onchange="nonaktif_excel()">
			</div>

		</div>

		<!-- <div class="row">
			<div class="col-3 col-m-3">Total Tunj Prestasi Omzet</div>
			<div class="col-3 col-m-3">
				<input type="number" class="form-control" name="Total_Tunj_Prestasi_Omzen" autocomplete="off" required>
			</div>
		</div>

		<div class="row">
			<div class="col-3 col-m-3"> Subsidi Tunj Prestasi Omzet</div>
			<div class="col-3 col-m-3">
				<input type="number" class="form-control" name="Subsidi_Tunj_Prestasi_Omzen" autocomplete="off" required>
			</div>
		</div> -->

		<div class="row" align="center" style="padding-top:0px;">
			<input type="submit" name="btn-cek" id="btn-cek" value="CEK" onclick="aktif_excel()"/>
			<input type="submit" name="btn-submit" id="btn-submit" value="EXCEL" onclick="laporan_excel()" disabled/>
		</div>
	</div>
	<?php echo form_close(); ?>


	<table width="100%" class="dataTable" summary="table">
		<thead id='isihead'>
			<tr>
				<th></th>
			</tr>
		</thead>
		<tbody id="isi"></tbody>
	</table>

</div> <!-- /container -->


<script>

    $(document).ready(function() {
		$('#dp1, #dp2').datepicker({
			format: "mm/yyyy",
			autoclose: true,
			viewMode: "months", 
			minViewMode: "months"
			// changeMonth: true,
			// changeYear: true,
			// showButtonPanel: true,
			// onClose: function(dateText, inst) { 
			// $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth,1 ));
			// alert(1);
			// }
		});

	});


	function nonaktif_excel() {
        document.getElementById("btn-submit").disabled = true;
    }

    function aktif_excel() {

        document.getElementById('isi').innerHTML='<tr><td colspan="5">Loading...</td></tr>';

		var kd_slsman = document.getElementById('Kd_Slsman').value;
		var dp1 	 = document.getElementById('dp1').value;
		var dp2 	 = document.getElementById('dp2').value;

		var bln1 = dp1.substring(0, 2);
		var thn1 = dp1.substring(dp1.length - 4);
		var bln2 = dp2.substring(0, 2);
		var thn2 = dp2.substring(dp2.length - 4);

		// document.getElementById('dealer').innerHTML='<option value="">Loading</option>';

		
		var get = ' api=APITES';
			get += '&Kd_Slsman='+kd_slsman;
			get += '&BulanAwal='+bln1;
			get += '&TahunAwal='+thn1;
			get += '&BulanAkhir='+bln2;
			get += '&TahunAkhir='+thn2;
			

			console.log(get);
			$.ajax({
				type 	: 'POST',	
				url 	: '<?php echo(site_url("HitungDendaPerFaktur/ceklistdata"));?>',
				data   	: get,
				success : function(dataa) {
					// document.write(dataa);
					// alert (dataa);

					var hasil = JSON.parse(dataa); 	
					var tamhead='';
					var tamp='';
					
					tamhead +='<tr>';
					tamhead +='<td><b>Periode</b></td>';
					tamhead +='<td style="text-align:right;"><b>Total Tunjangan Prestasi</b></td>';
					tamhead +='<td style="text-align:right;"><b>Total TP Penjualan</b></td>';
					tamhead +='<td style="text-align:right;"><b>Total Subsidi Penjualan</b></td>';
					tamhead +='<td style="text-align:right;"><b>Total Denda Penjualan</b></td>';
					tamhead +='</tr>';

					document.getElementById('isihead').innerHTML=tamhead;

					if(hasil.code=='1'){
						document.getElementById("btn-submit").disabled = false;

						for(i = 0; i < hasil.data.length; i++){		
							tamp +='<tr>';
							tamp +='<td>'+hasil.data[i].Periode+'</td>';
							tamp +='<td style="text-align:right;">'+format_number(hasil.data[i].TotalTunjangan, 0, ',', '.')+'</td>';
							tamp +='<td style="text-align:right;">'+format_number(hasil.data[i].TP_Penjualan, 0, ',', '.')+'</td>';
							tamp +='<td style="text-align:right;">'+format_number(hasil.data[i].Subsidi_Penjualan, 0, ',', '.')+'</td>';
							tamp +='<td style="text-align:right;">'+format_number(hasil.data[i].PotonganDenda, 0, ',', '.')+'</td>';
							tamp +='</tr>';

						}	

						
					} else {
						document.getElementById("btn-submit").disabled = true;
						tamp +='<tr>';
						tamp +='<td>Data Tidak Ada</td>';
						tamp +='</tr>';
						
					}
					document.getElementById('isi').innerHTML=tamp;
				}
			});
		
    }

	function format_number(number, decimals, decPoint, thousandsSep){
        decimals = decimals ||  0;
        number = parseFloat(number);

        if(!decPoint || !thousandsSep){
            decPoint = '.';
            thousandsSep = ',';
        }

        var roundedNumber = Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';
        // add zeros to decimalString if number of decimals indicates it
        roundedNumber = (1 > number && -1 < number && roundedNumber.length <= decimals)
                ? Array(decimals - roundedNumber.length + 1).join("0") + roundedNumber
                : roundedNumber;
        var numbersString = decimals ? roundedNumber.slice(0, decimals * -1) : roundedNumber.slice(0);
        var checknull = parseInt(numbersString) || 0;
    
        // check if the value is less than one to prepend a 0
        numbersString = (checknull == 0) ? "0": numbersString;
        var decimalsString = decimals ? roundedNumber.slice(decimals * -1) : '';
        
        var formattedNumber = "";
        while(numbersString.length > 3){
            formattedNumber = thousandsSep + numbersString.slice(-3) + formattedNumber;
            numbersString = numbersString.slice(0,-3);
        }

        return (number < 0 ? '-' : '') + numbersString + formattedNumber + (decimalsString ? (decPoint + decimalsString) : '');
    }

	function laporan_excel() {
		var kd_slsman = document.getElementById('Kd_Slsman').value;
		var nm_slsman = $("#Kd_Slsman option:selected").attr('data-id');
;
		var dp1 	 = document.getElementById('dp1').value;
		var dp2 	 = document.getElementById('dp2').value;
		
		// alert (btoa(nm_slsman));

		var bln1 = dp1.substring(0, 2);
		var thn1 = dp1.substring(dp1.length - 4);
		var bln2 = dp2.substring(0, 2);
		var thn2 = dp2.substring(dp2.length - 4);

		var data = 'api=APITES';
			data += '&Kd_Slsman='+kd_slsman;
			data += '&Nm_Slsman='+btoa(nm_slsman);
			data += '&BulanAwal='+bln1;
			data += '&TahunAwal='+thn1;
			data += '&BulanAkhir='+bln2;
			data += '&TahunAkhir='+thn2;
		window.open('<?php echo base_url().'HitungDendaPerFaktur/ProsesExcel?'; ?>'+data, '_blank');


	}


	
</script>


