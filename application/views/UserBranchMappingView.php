<script>
	var mode='';
	$(document).ready(function(){
		var oTable = $("#tblData").dataTable({
			'aoColumns':[
			{'sWidth' : '10%','sClass': 'left'},
			{'sWidth' : '20%'},
			{'sWidth' : '25%'},
			{'sWidth' : '15%'},
			{'sWidth' : '15%','sClass': 'center'},
			{'sWidth' : '10%','sClass': 'center'}
			],
			'aaSorting' : [[0,'asc']],
			'bProcessing' : true,
			'bServerSide' : true,
			'sAjaxSource' : site_url+'Datasource/UserBranchMapping',
			'iDisplayLength': 50,
			'aoColumnDefs' : 
			[
				{ 
				'bSortable' : false,
				'aTargets' : [0,1]
				}
			],
			'pagingType' : 'full_numbers',
			'dom' : '<"top"if<"clearfix">><l><p><"clearfix">rt'
		});

		/*$("#tblData").on('click','.btnDelete',function(){
			if(confirm("Are you sure to delete mapping ?"))
			{
				var data = $(this).attr('data');
				var csrf_bit = $('input[name=csrf_bit]').val();
				$(".loading").show();
				$.post
				(
					'<?php echo site_url('UserWorkgroupMapping/doDelete'); ?>',
					{ 
						'data' : data,
						'csrf_bit' : csrf_bit
					},
					function(res)
					{
						if(res.error != undefined)
							alert(res.error);
						else
							oTable.fnStandingRedraw();
						$(".loading").hide();
					},'json',errorAjax
				);
			}
		});*/
	});
</script>

<div class="wrapper">
	<div class="form_title">Mapping User - Cabang</div>
	<div class="button-bar-container">
		<?php /*if($this->session->userdata('can_add')) {*/ ?>
			<a href='<?php echo(site_url("UserBranchMapping/Add"));?>'><div class="btn btnAdd" id="btnAdd" style="width:300px !important;">Tambah Mapping User-Cabang</div></a>
		<?php /*}*/ ?>
	</div>

	<div class="clearfix"></div>

	<div class="wrapper_dataTable">
		<table id="tblData" class="display" width="100%">
			<thead>
				<tr>
					<th></th>
					<th>User ID</th>
					<th>Nama Karyawan</th>
					<th>Level Karyawan</th>
					<th>Cabang</th>
					<th>Aktif</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
<?php
	echo form_open();
	echo form_close();
?>
</div>