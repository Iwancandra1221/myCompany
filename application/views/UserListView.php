<style>
	.glyphicon { font-size:20px;margin-left:5px;margin-right:5px; }
	.merah { color:#c91006; }
	.hijau { color:#0ead05;}
	@media only screen and (max-width: 460px){
		.column-pc{
			display: none;
		}
		.item-mobile{
			display: block !important;
		}
	}
</style>

	<div class="container">
		<div>
			<div class="row" style="border: solid black 1px;margin: 0px; ">
				<div class="col-8 col-m-4">
					<input type="checkbox" name="aktif" id="aktif" value="1" checked> Aktif<br>
					<input type="checkbox" name="tdk_aktif" id="tdk_aktif" value="1"> Tidak Aktif <br> 
				</div>
				<div class="col-4 col-m-4" align="right">
					<button onclick="openPopup()">Import Salesman</button> 
				</div> 
			</div>
			<br>
		</div>
		<table id="TblUser" class="table table-striped table-bordered"></table>
	</div> <!-- /container -->

	 
<script> 
    function openPopup() { 
   		var result = confirm("Apakah Anda yakin ingin import data salesman ?");

		if (result) { 

      		$(".loading").show();
			$.ajax({ 
	        	type: 'GET', 
	        	url: '<?php echo site_url("UserControllers/loadSalesman") ?>',  
	        	dataType: 'json',
	        	success: function (data) {    
	        		//console.log(data);   
      				$(".loading").hide();
	        		alert(data); 
	        	}
	        }); 
    	} 
	} 

	$(document).ready(function() {
		$('#aktif,#tdk_aktif').change(function () {
			if(document.getElementById('aktif').checked==true){
				var aktif = 1;
			}else{
				var aktif = 0;
			}

			if(document.getElementById('tdk_aktif').checked==true){
				var tdk_aktif = 1;
			}else{
				var tdk_aktif = 0;
			}
	      	$("#TblUser").dataTable().fnFilter(
	      		aktif,0,
	      		tdk_aktif,0,
	      	);
	    });

	    $('#TblUser').dataTable( {
	        "bProcessing": true,
	        "bServerSide": true,
	         "columnDefs": [
				{"title":"ID User","targets": 0,"className":"column-pc"},
				{"title":"Nama User","targets": 1,"className":"column-pc"},
				{"title":"Posisi","targets": 2,"className":"column-pc"},
				{"title":"Cabang","targets": 3,"className":"column-pc"},
				{"title":"Status","targets": 4, "className":"column-pc"},
				{"title":"Aksi","targets": 5, "orderable": false}
		      ],

	        "sAjaxSource": '<?php echo site_url('UserControllers/data_user') ?>',
			"fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
                aoData.push({ "name": "active", "value": $('#aktif').is(":checked") ? 1 :0 });
                aoData.push({"name": "noactive", "value": $('#tdk_aktif').is(":checked") ? 1 :0 }); //pushing custom parameters
                oSettings.jqXHR = $.ajax( {
                    "dataType": 'json',
                    "type": "GET",
                    "url": sSource,
                    "data": aoData,
                    "success": fnCallback
                } );
            },
	        "oLanguage": {
		        "sLengthMenu": "Menampilkan _MENU_ Data per halaman",
		        "sZeroRecords": "Maaf, Data tidak ada",
		        "sInfo": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
		        "sInfoEmpty": "Menampilkan 0 s/d 0 dari 0 data",
		        "sSearch": "",
		        "sInfoFiltered": "",
		        "oPaginate": {
			       	"sPrevious": "Sebelumnya",
			        "sNext": "Berikutnya"
		    	}
		    }
	    });


	});



</script>  
