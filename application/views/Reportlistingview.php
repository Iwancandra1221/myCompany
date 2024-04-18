<script> 
	$(document).ready(function() {  
	}); 

	function filter(Tipe,Report,jumlah_hari=0){  
		var data ='&Tipe='+Tipe; 
		data +='&Report='+Report;
		data +='&jumlah_hari='+jumlah_hari;
  		document.getElementById('isibrach').innerHTML="";
  		$('.loading').show();
		$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo site_url('Reportlisting/loadData') ?>', 
					data  	: data,
					success : function(data) { 
						var isi_table = ''; 
						obj = JSON.parse(data);
						var no=1;

						$("#table tbody tr").remove(); 
						for (var i = 0; i < obj.length; i++) {   
							isi_table +='<tr>'; 
							isi_table +='<td>'+obj[i].No_Kelompok+'</td>';
							isi_table +='<td>'+obj[i].No_Faktur+'</td>';
							isi_table +='<td>'+obj[i].Tgl_Faktur+'</td>';
							isi_table +='<td>'+obj[i].Nm_Plg+'</td>';
							isi_table +='<td>'+obj[i].User_Name+'</td>'; 
							isi_table +='<td>'+obj[i].Entry_Time+'</td>';
							isi_table +='<td>'+obj[i].Keterangan+'</td>';  
							isi_table +='</tr>';
							no++;
						} 
						$("#table tbody").append(isi_table);
						$('.loading').hide();
					}

				});	
	}
</script> 

<style type="text/css"> 
	.tableWrap {
	  height: 550px; 
	  overflow: auto;
	} 
	thead tr th {
	  position: sticky;
	  top: 0;
	} 
	table {
	 border-collapse: collapse;
	} 
	th {
	  padding: 16px;
	  padding-left: 15px;
	  border-left: 1px dotted rgba(200, 209, 224, 0.6);
	  border-bottom: 1px solid #e8e8e8;
	  background: #ffc491;
	  text-align: left; 
	  box-shadow: 0px 0px 0 2px #e8e8e8;
	} 
	table {
	  width: 100%;
	  font-family: sans-serif;
	}
	table td {
	  padding: 16px;
	}
	tbody tr {
	  border-bottom: 2px solid #e8e8e8;
	}
	thead {
	  font-weight: 500;
	  color: rgba(0, 0, 0, 0.85);
	}
	tbody tr:hover {
	  background: #e6f7ff;
	} 
</style>
<div class="container">
	<div>
		<div class="row" style="border: solid black 1px;margin: 0px;">  
			<form id="f-filter"> 
				<div class="col-2">
					<select  id="Tipe" name="Tipe" style="width:100%;">
						<option value="F">Faktur</option> 
						<option value="M">Mutasi</option> 
					</select>
				</div> 
				<div class="col-3">
					<select  id="Report" name="Report" style="width:100%;">
						<option value="A1">Belum digroup</option> 
						<option value="A2">Belum dilisting</option> 
						<option value="A3">Belum dicetak gudang</option> 
						<option value="A4">Transaksi gagal</option> 
					</select>
				</div>
				<div class="col-3">
					Periksa &nbsp;
					<input type="text" id="jumlah_hari" name="jumlah_hari" value="15" style="width:30%;"> &nbsp; 
					Hari Terakhir
				</div>
				<div class="col-4"> 
					<span style="margin-left: 20px;display: inline-block;"></span>
					<input type="button" class="btn" value="Filter" style="float: right;" onclick="filter(document.getElementById('Tipe').value,document.getElementById('Report').value,document.getElementById('jumlah_hari').value)">
				</div>
			</form> 
		</div> 
	</div>
	<br>
	<div class="tableWrap">
		<table id="table" name="table" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<thead>
				<tr> 
					<th style="width:10%">No Kelompok</th>
					<th>No Faktur</th>
					<th>Tgl Faktur</th>
					<th>Nm Plg</th>
					<th>User Name</th>
					<th>Entry Time</th> 
					<th>Keterangan</th> 
				</tr>
			</thead>
			<tbody id="isibrach"> 
			</tbody>
		</table>
	</div>
</div> 