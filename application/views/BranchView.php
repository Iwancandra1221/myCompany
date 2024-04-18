<div class="container">
	<div class="row">
		<div class="page-title">BRANCH</div>
		<div class="col-12">
			<div align="right">
				<button onclick="sync()">Sync Branch</button>
			</div>
			<table id="table" class="table table-striped table-bordered" cellspacing="0" width="100%" summary="Table Branch">
				<thead>
					<tr>
						<th id="No" style="text-align:center">No</th>
						<th id="BranchID">BranchID</th>
						<th id="Branch_Name">Branch Name</th>
						<th id="Branch_Head">Branch Head</th>
						<th id="IsActive">IsActive</th>
						<th id="Updated_By">Updated By</th>
						<th id="Updated_Date">Updated Date</th>
						<th id="Action" width="80px">Action</th>
					</tr>
				</thead>
				<tbody id="isibrach">
					<tr>
						<td colspan="8">Loading...</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	view();

	function sync(){
		$.ajax({
			type 	: 'GET',	
			url 	: '<?php echo site_url('ZenSync/MsBranchSync'); ?>', 
			data  	: '',
			success : function() {
				if (confirm("Sync sudah berhasil, apakah ingin mereload halaman?") == true) {
					location.reload(); 
				}
			}

		});
	}
    function view(){

				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo site_url('ZenSync/MsBranchList'); ?>', 
					data  	: '',
					success : function(data) {

						var isi_table = '';
						var UpdatedBy = '';
						var UpdatedDate = '';
						var checked = '';

						obj = JSON.parse(data);
						var no=1;

						for (var i = 0; i < obj.length; i++) {

							if(obj[i].UpdatedBy==null){
								UpdatedBy = '-';
								UpdatedDate = '-';
							}else{
								UpdatedBy = obj[i].UpdatedBy;
								UpdatedDate = obj[i].UpdatedDate;
							}

							if(obj[i].IsActive=='Active'){
								checked = 'checked';
							}else{
								checked = '';
							}

							var onclick = "active_action('"+obj[i].BranchID+"')";



							isi_table +='<tr>';
							isi_table +='<td>'+no+'</td>';
							isi_table +='<td>'+obj[i].BranchID+'</td>';
							isi_table +='<td>'+obj[i].BranchName+'</td>';
							isi_table +='<td>'+obj[i].BranchHead+'<br>'+obj[i].UserName+'</td>';
							isi_table +='<td id="status_'+obj[i].BranchID+'">'+obj[i].IsActive+'</td>';
							isi_table +='<td>'+UpdatedBy+'</td>';
							isi_table +='<td>'+UpdatedDate+'</td>';
							isi_table +='<td><input type="checkbox" '+checked+' id="'+obj[i].BranchID+'" onclick="'+onclick+'"> Active</td>';
							isi_table +='</tr>';
							no++;
						}

						document.getElementById('isibrach').innerHTML=isi_table;
						$('#table').DataTable({
				        	"pageLength": 10
				      	});
					}

				});		

		}



		function active_action(e){
			var checkBox = document.getElementById(e);
			if (checkBox.checked == true){
			    document.getElementById('status_'+e).innerHTML='Active';
			    var status = 1;
			} else {
			    document.getElementById('status_'+e).innerHTML='Not Active';
			    var status = 0;
			}

			var data = 'BranchID='+e;
				data += '&Status='+status;
			console.log(data);
			$.ajax({
				type 	: 'POST',	
				url 	: '<?php echo site_url('ZenSync/UpdateStatus'); ?>', 
				data  	: data
			});

		}

</script>
