<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>
	
	td.empty{
		padding:50px;
		font-size:200%;
		text-align:center;
	}
</style>
<div class="container">
	<div class="form_title">
		<center>
			SYNC MASTER B2B
		</center>
	</div>
	<br>
	<?php echo form_open('Sync/SyncStart',array('id' => 'myform')); ?>
	<div class="border20 p20 mb20">
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Database</big></div>
			<div class="col-9 col-m-8">
				<select class="form-control form-control-dark" id="databaseId" name="databaseId" onchange="javascript:LoadLogs()" required>
					<option value="">Pilih Database</option>
					<?php
						foreach($databases as $database){
							echo "<option value='".$database->DatabaseId."'>".$database->NamaDb."</option>";
						}
					?>
				</select>
			</div>
		</div>
		
		<div id="add_more" class="row" style="margin:0">
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"></div>
			<div class="col-9 col-m-8"><button type="submit" class="btn btn-dark">Sync Master</button></div>
		</div>
	</div>
	<?php echo form_close(); ?>
	
	<div class="form_title mb20">
		<center>
			LAST SYNC
		</center>
		<button type="button" class="btn btn-dark" style="float:right" onclick="javascript:LoadLogs()">Refresh</button>
	</div>
	<br>
	<table id="table" class="table table-bordered">
		<thead>
			<tr>
				<th width="5%">NO</th>
				<th width="20%">LOG DATE</th>
				<th width="20%">USER</th>
				<th width="*">DATABASE</th>
				<th width="20%">STATUS</th>
			</tr>
		</thead>
		<tbody>
			<tr><td colspan="5" class="empty"><h1>Loading...</h1></td></tr>
		</tbody>
	</table>
</div>

<script>
	 $(document).ready(function() { 
		 $("#myform").submit(function(e) {
			e.preventDefault();
			$('.loading').show();
			var databaseId = $('#databaseId').val();
			$.ajax({ 
				type: 'POST', 
				url: $(this).attr('action'),  
				data: {
						'databaseId': databaseId
					}, 
				dataType: 'json',
				success: function (data) {
					$('.loading').hide();
					if(data.result=='SUCCESS'){
						StartSync(databaseId, data.tanggal);
					}
					else{
						alert(data.result+'\n'+data.error.replace(/\\n/g,"\n"));
					}
				}
			});
		});
	});
	
	function StartSync(databaseId, tanggal){
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url() ?>Sync/SyncData',  
			data: {
					'databaseId': databaseId,
					'tanggal': tanggal
				}, 
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				if(data.result=='SUCCESS'){
					alert("SINKRONISASI SELESAI");
					LoadLogs();
				}
				else{
					alert(data.result+'\n'+data.error.replace(/\\n/g,"\n"));
				}
			}
		});
	
	
	}
	
	function LoadLogs(){
		var databaseId = $('#databaseId').val();
		$('#table tbody').html('<tr><td colspan="5" class="empty"><h1>Loading...</h1></td></tr>');
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("Sync/GetLogSync") ?>?databaseId='+databaseId,
			dataType: 'json',
			success: function (data) {
				var html = '';
				var no = 0;
				for(i=0;i<data.length;i++){
					no +=1;
					html+='<tr><td>'+no+'</td><td>'+data[i].LogDate+'</td><td>'+data[i].UserID+'</td><td>'+data[i].TrxID+'</td><td>'+data[i].Remarks+'</td></tr>';
				}
				
				if(data.length==0){
					html+='<tr><td colspan="5" class="empty"><h1>Tidak ada data</h1></td></tr>';
				}
				
				$('#table tbody').html(html);
			}
		});
	}
	
		
	$( document ).ready(function() {
		LoadLogs();
		// setInterval(function(){
		   // LoadLogs();
		// }, 5000);
	});

</script>

