	<script>
	$(document).ready(function() {
		var reportOpt = "<?php echo($master_opt);?>";
		// $("#report_opt").val(reportOpt);
		// loadList(reportOpt);

    //     $('#example').DataTable({
		// 	"pageLength": 25,
		// 	columnDefs: [
		// 		{ targets: 'no-sort', orderable: false }
		// 	],
		// 	"order": [[1, 'asc']],
			
		// 	"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
		// 		var index = iDisplayIndex +1;
		// 		$('td:eq(0)',nRow).html(index);
		// 		return nRow;
		// 	},
			
		// });
		// $("#report_opt").change(function() {   	 
		// 	var reportPOT = $(this).val(); 
		// 	loadList(reportOpt);
		// });
		$('#report_opt').change(function () {
	  	 	$("#example").dataTable().fnFilter(
	   			document.getElementById('report_opt').value,0,
	     	);
	   });



      $('#example').dataTable( {
          "bProcessing": true,
          "bServerSide": true,
           "columnDefs": [
            {"title":"No","targets": 0},
            {"title":"Group","targets": 1},
            {"title":"Nama Group","targets": 2},
            {"title":"Partner Type","targets": 3},
            {"title":"Wilayah","targets": 4},
            {"title":"Kota","targets": 5},
            {"title":"Modified By","targets": 6},
            {"title":"Modified Date","targets": 7},
            {"title":"Edit","targets": 8, "orderable": false},
            {"title":"Delete","targets": 9, "orderable": false}
            ],

          "sAjaxSource": '<?php echo site_url('MasterReportWilayah/ListData'); ?>',
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

	// var loadList = function(reportOpt) {
	// 	$.ajax({ 
	// 			type: 'GET', 
	// 			url: '<?php echo site_url("MasterReportWilayah/LoadListByReprotOPT?id='+reportOpt+'") ?>',
	// 			dataType: 'json',
	// 			success: function (data){ 
	// 				if(data.length>0){  
	// 	 				var table = $('#example').DataTable(); 
	// 	 				table.clear().draw();
	// 					for (var i = 0; i < data.length; i++) {    
	// 						$('#example').DataTable().row.add([ 
	// 						  i,
	// 						  data[i].Grup,
	// 						  data[i].WilayahGroup,
	// 						  data[i].PartnerType,
	// 						  data[i].Wilayah,
	// 						  //data[i].Kota,
	// 						  data[i].ModifiedBy,
	// 						  data[i].ModifiedDate,
	// 						  '<a href = "MasterReportWilayah/update_page?id='+data[i].id+'"><i class="glyphicon glyphicon-pencil">' ,
	// 						  '<a onclick="delete_data('+"'"+data[i].id+"'"+')"  ><i class="glyphicon glyphicon-trash merah"></i></a>' 
	// 						]).draw();  
	// 					} 
	// 				} 
	// 			}
	// 		});
	// }

							function delete_data(id){
                if (confirm("Apakah anda yakin ingin menghapus config group wilayah report ini?") == true) { 
                      var data ='&id='+id;  
                      $.ajax({
                        type      : 'POST', 
                        url       : '<?php echo site_url('MasterReportWilayah/delete_data?') ?>',
                        data      : data,
                        success   : function(data) { 
                          var data = data.trim();
                          if(data=='1'){ 
                              //ocation.reload(); 
                              window.location.href='<?php echo site_url('MasterReportWilayah') ?>';
                          } 
                          return false;

                        }

                      })
                }
              }

	
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
</script>  
<style>
	table.dataTable thead .sorting:after {
    opacity: 0.2;
    content: "";
	}
	
	table.dataTable thead .sorting_asc:after {
    content: "";
	}
	table.dataTable thead .sorting_desc:after {
    content: "";
	}
</style>

<div class="container">
	<div class="page-title"><?php echo(strtoupper($title));?></div>
	<?php //if($access->can_create == 1) { ?>
	<a href="MasterReportWilayah/insert_page">Insert New</a>
	<?php //} ?> 
		<div class="row" id="div_supplier">
			<div class="col-1">Report OPT</div>
			<div class="col-3">
				<select  class="form-control" name="report_opt" id="report_opt" novalidate > 
					<option value="">ALL</option>
					<?php  
						foreach($ListReportOPT as $s)
						{ 
							echo("<option value='".$s->ConfigValue."'>".$s->ConfigValue."</option>"); 
						}			  
					?>
				</select>
			</div>
	  </div>   
	<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <?php 
			// echo "<thead>";
			// echo "<tr>";
			// echo "<th class='no-sort'>No</th>"; 
			// echo "<th>Group</th>";
			// echo "<th>Nama Group</th>";
			// echo "<th>Partner Type</th>";
			// echo "<th>Wilayah</th>";
			// //echo "<th>Kota</th>";
			// echo "<th>Modified By</th>";
			// echo "<th>Modified Date</th>";
      //       echo "<th class='no-sort'>Edit</th>";
      //       echo "<th class='no-sort'>Delete</th>";
			// echo "</tr>";
			// echo "</thead>";
			// echo "<tbody>";
			// $i = 1;
			/*foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>"; 
				echo "<td>".$r->Grup."</td>"; 
				echo "<td>".$r->WilayahGroup."</td>"; 
				echo "<td>".$r->PartnerType."</td>";
				echo "<td>".$r->Wilayah."</td>"; 
				echo "<td>".$r->Kota."</td>";
				echo "<td>".$r->ModifiedBy."</td>";
				echo "<td>".$r->ModifiedDate."</td>";
                echo "<td><a href = 'MasterReportWilayah/update_page?id=".$r->id."'><i class='glyphicon glyphicon-pencil'></td>";
                echo "<td><a href = '#' data-href='MasterReportWilayah/delete_data?id=".$r->id."' data-toggle='modal' data-target='#confirm-delete' data-record-title='".$r->WilayahGroup."'><i class='glyphicon glyphicon-trash'></a></td>"; 
				echo "</tr>";
				$i += 1;
			}*/
		// echo "</tbody>"; ?>
	</table>
</div> <!-- /container -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				Delete Confirmation
			</div>
			<div class="modal-body">
                <p>You are about to delete <b><i class="title"></i></b> record, this procedure is irreversible.</p>
                <p>Do you want to proceed?</p>
                <!-- <p class="debug-url"></p> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<a class="btn btn-danger btn-ok">Delete</a>
			</div>
		</div>
	</div>
</div>
<script>
	$('#confirm-delete').on('show.bs.modal', function(e) {
		$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
		$('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
	});
	
	$('#confirm-delete').on('show.bs.modal', function(e) {
		var data = $(e.relatedTarget).data();
		$('.title', this).text(data.recordTitle);
	});
</script>
