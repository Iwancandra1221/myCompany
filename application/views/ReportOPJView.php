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
        echo form_open("ReportOPJ/RekapOpjFaktur_Proses", array("target"=>"_blank"));		
	?>

	<div class="form-container">
		
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
			<div class="col-3">Partner Type</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="partnertype" id="partnertype">
                    <option value='all' selected>ALL</option>

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
				<select  class="form-control" name="wilayah" id="wilayah">
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
				<select  class="form-control" name="salesman" id="salesman" onchange="fsalesman()">
                    <option value='all' selected>ALL</option>

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
			<div class="col-3">Pilihan Report</div>
			<div class="col-8 col-m-8 ">
				<select  class="form-control" name="report" id="report">
                    <option value='detail'>Detail</option>
                    <option value='summary'>Summary</option>
                    <option value='sisatoko'>Sisa Per Toko</option>
                    <option value='sisawil'>Sisa Per Wilayah</option>					
				</select>
			</div>
		</div>
        		

        <div class="row" align="center" style="padding-top:50px;">			
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


	// function fsalesman() {
	// 	var cbo = document.getElementById("salesman");
	// 	var txt_value = document.getElementById("nmsalesman");
		
	// 	if (cbo.value == "ALL") {
	// 		txt_value.value = "";
	// 	} else {			
	// 		txt_value.value = "'" + cbo + "'";				
	// 	}
	// }	

	// fsalesman();


</script>





