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


	.listscroll {
	  overflow-y: scroll; 
	  max-height: 180px;
	}
</style>
<script>
    $(document).ready(function() {  
		$('#dp1').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var StartDt = $('#dp1').datepicker('getDate');
			$('#dp2').datepicker("setStartDate", StartDt);
		}); 
		$('#dp2').datepicker({
			format: "mm/dd/yyyy",
			autoclose: true
		}).on('changeDate', function(e) { //changeDate
			var EndDt = $('#dp2').datepicker('getDate');
			$('#dp1').datepicker("setEndDate", EndDt);
		});
		hidemenu(0);
	} );
</script>
<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
  <?php echo form_open($formDest, array("target"=>"_blank")) ?>
	<div class="form-container">
 
		<div class="row" >
			<div class="col-3 col-m-3">
				<input type="radio" name="kategori" id="p0" onchange="javascript:hidemenu(0)" checked> <label for="p0">Gudang</label> </div>
			<div class="col-8 col-m-2 date">
				<select id="cboGudang" name="cboGudang" class="form-control">
		            <option value='' selected>ALL</option>
		            <?php
		              $jum= count($listgudang->data);
		              for($i=0; $i<$jum; $i++){		
		              	echo "<option value='".$listgudang->data[$i]->Kode_gudang."'>".$listgudang->data[$i]->Kode_gudang." | ".$listgudang->data[$i]->Nama_gudang."</option>";
		              }	 
		            ?>
          		</select>
      		</div>
		</div>  
		<div class="row" >
			<div class="col-3 col-m-3">
				<input type="radio" name="kategori" id="p1" onchange="javascript:hidemenu(1)"> <label for="p1">Group Gudang</label> </div>
			<div class="col-8 col-m-2 date">
				<select id="cboGroupGudang" name="cboGroupGudang" class="form-control">  
		            <option value='' selected></option>
		            <?php
		              $jum= count($listgroupgudang->data);
		              for($i=0; $i<$jum; $i++){		
		              	echo "<option value='".$listgroupgudang->data[$i]->Kd_GroupGudang."'>".$listgroupgudang->data[$i]->Kd_GroupGudang." | ".$listgroupgudang->data[$i]->Nm_GroupGudang."</option>";
		              }	 
		            ?>
          		</select>
      		</div>
		</div>  

		<div class="row" >
			<div class="col-3 col-m-3"> </div>
			<div class="col-8 col-m-2 date"> 
				<div class="listscroll" style="height:200px;">
				<ul id="listgudang" name="listgudang" data-role="listview"> 
				</ul> 
				<input type='hidden' id='listgd' name='listgd' value=''>
				</div>
      		</div>
		</div>  


		<div class="row">
			<div class="col-3 col-m-3">Periode</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dp1" placeholder="mm/dd/yyyy" name="dp1" autocomplete="off" required>
			</div>
			<div class="col-1 col-m-1">SD</div>
			<div class="col-2 col-m-2 date">
				<input type="text" class="form-control" id="dp2" placeholder="mm/dd/yyyy" name="dp2" autocomplete="off" required>
			</div>
		</div>   
		 

      <div class="row" align="center" style="padding-top:50px;" id="div_pdf">  
         <input type = "submit" name="btnPdf" value="EXPORT PDF"/>
         <input type = "submit" name="btnExcel" value="EXPORT EXCEL"/>
      </div>
    </div>
	<?php echo form_close(); ?>
</div> 
<script type="text/javascript"> 
$("#cboGroupGudang").change(function() {    
	var kdgroupgudang = $(this).val();
	$('.loading').show();
	$.ajax({ 
		type: 'GET',
		url: '<?php echo site_url("Reportserahterimabpb/GetListGudangByKdGroupGudang?kdgroupgudang='+kdgroupgudang+'") ?>',
		dataType: 'json',
		success: function (data){   
			$('.loading').hide(); 
 			$('#listgudang').empty(); 
			if (data!="")
			{	
				var gudang = data.split(";;");   
				var listkd = "";  
				var content = "";
				for (var i = 0; i < gudang.length; i++) {  
					content += '<li>' + gudang[i] + '</li>'; 
					var kdgd = gudang[i].split(" | ");

					if (listkd=="")
					{
						listkd = kdgd[0];
					}
					else
					{
						listkd += ","+kdgd[0];
					}

				} 
				$('#listgudang').append(content);
				document.getElementById("listgd").value = listkd; 
			}
		}
	}); 
});
function hidemenu(val){ 
		if(val==0){  
			$("#cboGudang").attr('disabled', false);
			$("#cboGroupGudang").attr('disabled', true);
			$("#cboGudang").prop('selectedIndex',0);
			$("#cboGroupGudang").prop('selectedIndex',-1); 
 			$('#listgudang').empty();
			document.getElementById("listgd").value = ""; 
		}
		else
		{
			$("#cboGudang").attr('disabled', true);
			$("#cboGroupGudang").attr('disabled', false);
			$("#cboGudang").prop('selectedIndex',-1);
			$("#cboGroupGudang").prop('selectedIndex',0);
 			$('#listgudang').empty();
			document.getElementById("listgd").value = ""; 
		}
	 }
</script>
	 
	  
	  
	  
 
 


