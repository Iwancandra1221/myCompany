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
	#no-rekening-value{
		color:black;
	}
	.datepicker{
		z-index: 100000;
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

		$("input[name='opsi']").click(function(){
			var value = $(this).val();
			if(value==1 || value==2){
				$("#no-rekening").css({"display":"none"});
			}
			else if(value==3){
				$("#no-rekening").css({"display":"block"});
				var noRekeningVal = $("select[name='no_rekening']").val();
				$("#no-rekening-value").val(noRekeningVal);
			}
		});
		$("select[name='no_rekening']").change(function(){
			var value = $(this).val();
			$("#no-rekening-value").val(value);
		})
		$("input[name='tipe_report']").click(function(){
			var value = $(this).val();

			$("#no_bbk_parent").css({"display":"none"});
			if(value=='bbk'){
				$("#no_bbk_parent").css({"display":"none"});
			}
			else if(value=='bkk'){
				$("#no_bbk_parent").css({"display":"block"});
				$("#getBbk_Bpkk").css({"display":"none"});
				$("#getBbk_Bkk").css({"display":"block"});
			}
			else if(value=='bpkk'){
				$("#no_bbk_parent").css({"display":"block"});
				$("#getBbk_Bpkk").css({"display":"block"});
				$("#getBbk_Bkk").css({"display":"none"});
			}
		});
	} );
</script>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php 
        echo form_open($formUrl, 
        	array(
        	"id"=>"f-filter",
        	)
        );
	?>
	<div class="form-container">
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Kode Barang</div>
        	<div class="row-input">
              	<input type="text" name="kd_brg" value="" class="form-control">
			</div>           
        </div>
        <div class="row">
           	<div class="col-3 col-m-3 row-label">Merk</div>
        	<div class="row-input">
              	<input type="text" name="merk" value="" class="form-control" readonly style="background: #a7a7a7;">
			</div>           
        </div>
		<div class="row">
           	<div class="col-3 col-m-3 row-label">Jenis Barang</div>
        	<div class="row-input">
              	<input type="text" name="jenis_barang" value="" class="form-control" readonly style="background: #a7a7a7;">
			</div>           
        </div>
        <div class="row">
           	<div class="col-3 col-m-3 row-label">Nomor Seri</div>
        	<div class="row-input">
              	<input type="text" name="no_seri" value="" class="form-control">
			</div>           
        </div>

        <div class="row" align="center" style="padding-top:50px;">
			<input type="button" onclick="filterTblUser()" name="submit" value="CHECK"/>
		</div>
	</div>
	<?php echo form_close(); ?>
	<br>
	<table id="tb-report-checklist" style=""></table>


	<script>
		$(document).ready(function(){
			var formData = new FormData();
			formData.append('submit','CHECK');
			var object = {};
			formData.forEach(function(value, key){
				object[key] = value;
			});
			TblreportChecklistData = object;
			tblReportChecklist = $('#tb-report-checklist').DataTable({
				//"order":[[0,"desc"]],
				responsive: true,
				"columnDefs": [
					{"title":"No","targets": 0, "orderable": false},
					{"title":"Nama Barang","targets": 1,"orderable": false},
					{"title":"No Seri","targets": 2,"orderable": false},
					{"title":"Customer","targets": 3,"orderable": false},
					{"title":"Mobile","targets": 4,"orderable": false},
					{"title":"Lokasi","targets": 5,"orderable": false},
					{"title":"","targets": 6, "orderable": false}
				],
				"columns": [
					{ "data": "no" },
					{ "data": "nm_brg" },
					{ "data": "no_seri" },
					{ "data": "nm_plg" },
					{ "data": "hp" },
					{ "data": "kd_lokasi" },
					{ "data": "aksi" },
				],
				processing: true,
				serverSide: true,
				ajax: {
					url: "<?=base_url()?>Reportchecklistservice/",
					type:'POST',
					data: function(d){
						return $.extend(d,TblreportChecklistData);
					},
					"dataSrc": function ( json ) {
						return json.data;
					}   
				},
			});
		});
		function filterTblUser(){
			var object = {};
			var formData = new FormData($("#f-filter")[0]);
			formData.append('submit','CHECK');
			formData.forEach(function(value, key){
				object[key] = value;
			});
			TblreportChecklistData = object;
			$('#tb-report-checklist').DataTable().ajax.reload(null, true);
		}
		
	</script>
</div> <!-- /container -->