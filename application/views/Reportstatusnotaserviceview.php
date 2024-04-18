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

	.column {
	  float: left;
	  width: 50%;
	  padding: 10px;
	  height: 300px; /* Should be removed. Only for demonstration */
	}

	.listscroll {
	  overflow-y: scroll; 
	  max-height: 180px;
	}
</style>
<script>
    $(document).ready(function() {  

		$('#dpbln').datepicker({
			format: "MM yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date())
		.on('changeDate', function(e) { 
			var fields = this.value.split(' ');  
			var day = new Date(fields[0]+"/"+"01/"+fields[1]);  
			$("#dptglawal").datepicker('setDate', new Date(day.getFullYear(), day.getMonth()-3, 1));
			$("#dptglakhir").datepicker('setDate', new Date(day.getFullYear(), day.getMonth()+1, 0));
			$("#tglawal").datepicker('setDate', new Date(day.getFullYear(), day.getMonth()-3, 1));
			$("#tglakhir").datepicker('setDate', new Date(day.getFullYear(), day.getMonth()+1, 0)); 
		});  
	
		$('#tglawal').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()-3, 1));

		$('#tglakhir').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()+1, 0));	

 
		$('#dptglawal').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()-3, 1));

 
		$('#dptglakhir').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()+1, 0));
  
		on_change_chk();
 
		$("#btnProses").click(function(e) { 
			var kodenota = "";
			var garansi = "";
			var selesai = "";
			var batal = "";
			var bayar = "";

			if (!$('#chksemuakodenota').prop('checked'))
			{ 
				kodenota = document.getElementById("cbokodenota").value;
			} 
 
			if (!$('#chkGaransiSemua').prop('checked')) 
				garansi = (($('#chkGaransi').prop('checked')) ? 'Y' : 'N');   

			if (!$('#chkSelesaiSemua').prop('checked')) 
				selesai = (($('#chkSelesai').prop('checked')) ? 'Y' : 'N');   

			if (!$('#chkBatalSemua').prop('checked')) 
				batal = (($('#chkBatal').prop('checked')) ? 'Y' : 'N');   

			if (!$('#chkBayarSemua').prop('checked')) 
				bayar = (($('#chkBayar').prop('checked')) ? 'Y' : 'N');   


			var tglawal = document.getElementById("dptglawal").value;
	    	e.preventDefault();
	    	$(".loading").show(); 


     		var table = $('#table_detail').DataTable(); 
			table.clear().draw();
		 	table.destroy();
			$('#table_detail').dataTable({searching: false, paging: false, info: false, order: false});

	    	$.ajax({
	        type: "POST",
	        url: '<?php echo site_url("Reportstatusnotaservice/proses") ?>',  
        	  data: { tglawal: tglawal, kodenota: kodenota, garansi: garansi, selesai: selesai, batal: batal, bayar: bayar
        		}, 
	        success: function(result) {
	            $(".loading").hide();
	            if (result!="PROSES SUDAH SELESAI") 
	            { 
						var result = JSON.parse(result); 
						for (var i = 0; i < result.length; i++) {  
							var garansi = '';
							if (result[i].Jaminan=='Y')
							{
								garansi = 'GARANSI';
							}
							else
							{ 
								garansi = 'NON-GARANSI';
							}

							$('#table_detail').DataTable().row.add([
				                '<p >'+date("d M Y",strtotime(result[i].Tgl_Svc))+'</p>',
				                '<p >'+result[i].No_Svc+'</p>',
				                '<p >'+garansi+'</p>' 
				                ]).draw();  
		            }  

 
	         		document.getElementById('tgltrans').innerHTML = 'Transaksi Bulan ' + document.getElementById("dpbln").value;


	         		document.getElementById('tglawalakhir').innerHTML = 'Periode : ' + date("d M Y",strtotime(document.getElementById("tglawal").value)) + ' s/d ' +date("d M Y",strtotime(document.getElementById("tglakhir").value));


	         		document.getElementById('txt_total').innerHTML = 'Total ' + result.length + ' Buah Transaksi';

	         		alert('Terdapat '+result.length+' buah transaksi Belum Selesai yang belum dibatalkan pada bulan '+date("d M Y",strtotime(tglawal))+'! Transaksi-transaksi tersebut akan ditampilkan dalam DAFTAR ! PERINGATAN: Report ini tidak akan ditampilkan! Silahkan BATALKAN terlebih dahulu transaksi-transaksi ini!');


						$('#model_datagantung').modal('show');

	            } 
	            else
	            { 
	         		alert(result);
	            }
	        },
	        error: function(result) {
	            $(".loading").hide();
	            alert('error');
	        }
	    });
	});
	} );

	function btnok_onclick()
	{
		$('#model_datagantung').modal('hide');
	}

	function btn_reset_Onclick()
	{
		$('#dpbln').datepicker({
			format: "MM yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date());  
	
		$('#tglawal').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()-3, 1));

		$('#tglakhir').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()+1, 0));	

 
		$('#dptglawal').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()-3, 1));

 
		$('#dptglakhir').datepicker({
			format: "dd-MM-yyyy",
			autoclose: true
		})
		.datepicker("setDate", new Date(new Date().getFullYear(), new Date().getMonth()+1, 0));

 
			document.getElementById('chkGaransiSemua').checked = true;
			document.getElementById("chkGaransi").disabled= true; 
			document.getElementById("chkGaransi").checked = false; 

			document.getElementById('chkSelesaiSemua').checked = true;
			document.getElementById("chkSelesai").disabled= true; 
			document.getElementById("chkSelesai").checked = false;
		 
			document.getElementById('chkBatalSemua').checked = true;
			document.getElementById("chkBatal").disabled= true; 
			document.getElementById("chkBatal").checked = false; 

			document.getElementById('chkBayarSemua').checked = true;
			document.getElementById("chkBayar").disabled= true; 
			document.getElementById("chkBayar").checked = false;

			document.getElementById('chksemuakodenota').checked = true;
			$("#cbokodenota").attr('disabled', true);
			$("#cbokodenota").prop('selectedIndex',-1); 
	}

	

	function on_change_chk()
	{
		if (document.getElementById('chkGaransiSemua').checked)  
		{
			document.getElementById("chkGaransi").disabled= true; 
			document.getElementById("chkGaransi").checked = false;
		}
	   else
	   	document.getElementById("chkGaransi").disabled= false; 

		if (document.getElementById('chkSelesaiSemua').checked)  {
			document.getElementById("chkSelesai").disabled= true; 
			document.getElementById("chkSelesai").checked = false;
		}
	   else
	   	document.getElementById("chkSelesai").disabled= false; 

		if (document.getElementById('chkBatalSemua').checked)  {
			document.getElementById("chkBatal").disabled= true; 
			document.getElementById("chkBatal").checked = false;
		}
	   else
	   	document.getElementById("chkBatal").disabled= false; 

		if (document.getElementById('chkBayarSemua').checked)  {
			document.getElementById("chkBayar").disabled= true; 
			document.getElementById("chkBayar").checked = false;
		}
	   else
	   	document.getElementById("chkBayar").disabled= false; 

		if (document.getElementById('chksemuakodenota').checked)  { 
			$("#cbokodenota").attr('disabled', true);
			$("#cbokodenota").prop('selectedIndex',-1);
		}
	   else 
	   {
	   	$("#cbokodenota").attr('disabled', false);
			$("#cbokodenota").prop('selectedIndex',0);
	   }
	}


	

</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container">  
		<div class="row">
			<div class="col-2 col-m-3">Periode Bulan</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dpbln"name="dpbln" autocomplete="off" required>
			</div>  
			<div class="col-8 col-m-3"><p style="color:#ffffff;">Tanggal mana pun yang Anda pilih. tanggal Awal untuk Periode kontrol tetap dimulai dari tanggal 1!</p></div>
		</div>  

		<div class="row">
			<div class="col-2 col-m-3">Periode Kontrol</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dptglawal"name="dptglawal" autocomplete="off" disabled> 
    			<input type="hidden" class="form-control" name="tglawal" id="tglawal" value="">
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dptglakhir"name="dptglakhir" autocomplete="off" disabled> 
    			<input type="hidden" class="form-control" name="tglakhir" id="tglakhir" value="">
			</div>
			<div class="col-5 col-m-3"><p style="color:#ffffff;">Periode Kontrol otomatis menjadi TIGA BULAN sebelumnya hingga bulan (dan tahun) yang dipilih!</p></div>
		</div>    
	</div>
	<div class="form-container">   
		<div class="row" >
			<div class="col-2 col-m-3">Kode Nota</div>
			<div class="col-3 col-m-2">
				<select id="cbokodenota" name="cbokodenota" class="form-control">   
		     		<?php
		       		$jum= count($listnota->data);
		        		for($i=0; $i<$jum; $i++){		
		        			echo "<option value='".$listnota->data[$i]->Kd_Nota."'>".$listnota->data[$i]->Kd_Nota."</option>";
		         	}	 
		      	?>
        		</select>
      	</div>
			<div class="col-3 col-m-2">
				<input type="checkbox" name="chksemuakodenota" id="chksemuakodenota" onclick="on_change_chk();" checked>
				<label for="chksemuakodenota" style="color:#03ddff;">Semua Kode Nota</label>
			</div>
		</div> 
    </div>

	<div class="row">
		<div class="column" > 
		  	<div class="form-container" style="height:250px;width:550px;">
				<div class="row" >
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkGaransi" id="chkGaransi" >
						<label for="chkGaransi" style="color:white;">Garansi</label>
					</div>
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkGaransiSemua" id="chkGaransiSemua" onclick="on_change_chk();" checked>
						<label for="chkGaransiSemua" style="color:#03ddff;">Semua Data</label>
					</div>
				</div>
				<div class="row" >
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkSelesai" id="chkSelesai" >
						<label for="chkSelesai" style="color:white;">Selesai (Kembali)</label>
					</div>
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkSelesaiSemua" id="chkSelesaiSemua" onclick="on_change_chk();" checked>
						<label for="chkSelesaiSemua" style="color:#03ddff;">Semua Data</label>
					</div>
				</div>
				<div class="row" >
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkBatal" id="chkBatal" >
						<label for="chkBatal" style="color:white;">Dibatalkan</label>
					</div>
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkBatalSemua" id="chkBatalSemua" onclick="on_change_chk();" checked>
						<label for="chkBatalSemua" style="color:#03ddff;">Semua Data</label>
					</div>
				</div>
				<div class="row" >
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkBayar" id="chkBayar" >
						<label for="chkBayar" style="color:white;">Bayar</label>
					</div>
					<div class="col-4 col-m-2">
						<input type="checkbox" name="chkBayarSemua" id="chkBayarSemua" onclick="on_change_chk();" checked>
						<label for="chkBayarSemua" style="color:#03ddff;">Semua Data</label>
					</div>
				</div>
			</div>
		</div>
		<div class="column" > 
			<div class="form-container" style="height:250px;width:550px;"> 
		      <div class="row" >     
					<div class="col-12 col-m-3">
						<p style="color:#ffffff;">Klik tombol "PROSES" terlebih dahulu dan tunggu hingga selesai!</p>
						<p style="color:#ffffff;">"REPORT" : Tempilkan ke Report!</p>
						<p style="color:#ffffff;">"EXCEL" : Exspor ke file Ms. Excel!</p>
					</div>
		      </div> 

		      <div class="row" align="center" >   
					<div class="col-4 "> 
						<button id='btnProses' style="background-color:lightgreen; width:160px; color:black;">PROSES</button>
		         	<!-- <input type = "button" style="background-color:lightgreen; width:160px;" name="btnProses" value="PROSES"/> -->
					</div>  
					<div class="col-4 ">
		         	<input type = "submit" style="background-color:white; width:160px;"name="btnPdf" value="EXPORT PDF"/>
					</div>  
					<div class="col-4 ">
		         	<input type = "submit" style="background-color:white; width:160px;" name="btnExcel" value="EXPORT EXCEL"/>
					</div>   
		      </div>


		      <div class="row" align="center" >  
					<div class="col-12">
		         	<input type="button" onclick="btn_reset_Onclick()" style="background-color:palevioletred; width:520px;"  name="btnReset" value="Reset"/>
					</div>
		      </div>
				</div>
		</div>
	</div> 
	<?php echo form_close(); ?>
</div>  


<div class="modal modal-tall fade"  id="model_datagantung" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog">
   	<div class="modal-content">
   		<div class="modal-header">
   			Transaksi Belum Selesai Yang Belum DiBatalkan
   		</div>
      	<div class="modal-body"> 


      	<div class="row">
  			<div class="col-11 " style ="text-align:center;">  
				<label id = "tgltrans" name = "tgltrans"></label><br>
				<label id = "tglawalakhir" name = "tglawalakhir" style =" color:#d107f0;" ></label>
     		 </div>
     		 </div>  

  			<div class="row">
  			<div class="col-11 col-m-8"> 
          	<table id="table_detail" class="table table-bordered" cellspacing="0" cellpadding="5px;" width="100%" > 
               <?php 
                  echo "<thead>";
                  echo "<tr>";
                  echo "<th>Run Start</th>";
                  echo "<th>Run End</th>"; 
                  echo "<th>Type</th>";    
                  echo "</tr>";
                  echo "</thead>";
                  echo "<tbody>"; 
                  echo "</tbody>"; 
                ?>
          	</table> 
     		 </div>
     		 </div> 

      	<div class="row">
  			<div class="col-7 " style ="color:#f00707;">  
				<label id = "txt_total" name = "txt_total"></label>
     		</div>
  			<div class="col-4 " style ="color:#000000;">  
     			<button type="button" onclick="btnok_onclick()" style="background-color:lightgreen;float: right;width:100px">Ok</button>
     		</div>
     		 </div> 

     		</div>
      </div>
   </div> 
</div>
	 
	  
	  
	  
 
 


