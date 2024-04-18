<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>

/* Add hover effect to DataTable rows */
#tb-faktur-list tbody tr:hover {
    background-color: #f5f5f5; /* Change the background color as desired */
    cursor: pointer; /* Change the cursor to a pointer */
}

</style>

<div class="container">
		<!-- load list faktur -->
		<div class="modal fade" id="m-browseFaktur" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
		<div class="modal-dialog" style="width: 80%;" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" style="text-align: center;">List Faktur</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				
				<div class="modal-body p30">
					<div class="container-fluid">
						<div class="row">
							<div class="col-xs-12">
								<div class="table-responsive">
									<table class="table table-bordered" id="tb-faktur-list">
										<thead>
											<!-- your header content here -->
										</thead>
										<tbody>
											<!-- your row data here -->
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<!-- <input type="submit" onclick="return confirm('Apakah anda yakin ingin tambah?')" class="btn btn-danger-dark" name="btn-submit" value="SAVE"> -->
					<button type="button" class="btn btn-dark" data-dismiss="modal">CLOSE</button>
				</div>
			</div>
		</div>
	</div>

	<div class="form_title">
		<div style="text-align: center;">
			HAPUS FAKTUR BASS
		</div>
	</div>
	<?php echo form_open('ReportFinance/PreviewSisaFaktur',array('id' => 'myform','target'=>'_blank')); ?>
	<div class="form-container border20 p20 mb20">
		
		<div class="row">
			<div class="col-3">NO FAKTUR</div>
			<div class="col-4">
				<input type="text" id="no_faktur" class="form-control form-control-dark">
			</div>
			<div class="col-5" >
				<input type="button" name="btnBrowseList" class="btn" value="..." id="btnBrowseList">
				<input type="button" name="btnBrowse" class="btn" value="CARI" onclick="javascript:cariFaktur()">
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">TGL FAKTUR</div>
			<div class="col-4">
				<input type="text" id="tgl_faktur" class="form-control form-control-dark" readonly>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">KODE BASS</div>
			<div class="col-9">
				<input type="text" id="kode_bass" class="form-control form-control-dark" readonly>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">NAMA BASS</div>
			<div class="col-9">
				<input type="text" id="nama_bass" class="form-control form-control-dark" readonly>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">NO PO</div>
			<div class="col-9">
				<input type="text" id="no_po" class="form-control form-control-dark" readonly>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3">STATUS</div>
			<div class="col-9">
				<input type="text" id="status" class="form-control form-control-dark" readonly>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3"></div>
			<div class="col-4"><input type="button" name="btnDelete" class="btn" value="HAPUS" onclick="javascript:HapusFaktur()"> <input type="button" name="btnReset" class="btn" value="RESET" onclick="javascript:Reset()"></div>
		</div>
		
		<div class="row" id="result">
			
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script>
	let table;

	function cariFaktur(){
		$('#result').html('');
		var no_faktur = $('#no_faktur').val();
		if(no_faktur!=''){
			$('.loading').show();
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url('ReportBass/CariFaktur') ?>',  
				data: {
						'no_faktur': no_faktur
					}, 
				dataType: 'json',
				success: function (msg) {
					// console.log(data);
					$('.loading').hide();
					if(msg.result=='sukses'){
						$('#tgl_faktur').val(msg.data.TANGGAL);
						$('#kode_bass').val(msg.data.KODE_BASS);
						$('#nama_bass').val(msg.data.NAMA_BASS);
						$('#no_po').val(msg.data.NO_PO);
						$('#status').val(msg.data.STATUS);
					}
					else{
						$('#result').html('<div class="msg msg-danger border20"><i class="glyphicon glyphicon-remove"></i> '+msg.error+'</div>');
						setTimeout(function() {
							Reset();
						}, 5000);
					}
				}
			});
		}
	}
	
	function HapusFaktur(){
		
		$('#result').html('');
		var no_faktur = $('#no_faktur').val();
		var no_po = $('#no_po').val();
		if(no_faktur!='' && no_po!=''){
			if (confirm('Ingin hapus data ini?')) {
				$('.loading').show();
				$.ajax({ 
					type: 'POST', 
					url: '<?php echo site_url('ReportBass/HapusFaktur') ?>',  
					data: {
							'no_faktur': no_faktur,
							'no_po': no_po
						}, 
					dataType: 'json',
					success: function (msg) {
						// console.log(msg);
						$('.loading').hide();
						if(msg.result=='sukses'){
							$('#result').html('<div class="msg msg-success border20"><i class="glyphicon glyphicon-ok-sign"></i> No Faktur Berhasil Dihapus!'+'</div>');
							setTimeout(function() {
								Reset();
							}, 5000);
						}
						else{
							$('#result').html('<div class="msg msg-danger border20"><i class="glyphicon glyphicon-ok-sign"></i> '+msg.error+'</div>');
							setTimeout(function() {
								Reset();
							}, 5000);
						}
					}
				});
			}
		}
	}
	
	function Reset(){
		$('#result').html('');
		$('#no_faktur').val('');
		$('#tgl_faktur').val('');
		$('#kode_bass').val('');
		$('#nama_bass').val('');
		$('#no_po').val('');
		$('#status').val('');
	}

	$('#btnBrowseList').click(function() {
		$("#m-browseFaktur").modal("show");

		$('.loading').show();

		if ($.fn.DataTable.isDataTable('#tb-faktur-list')) {
			$('#tb-faktur-list').DataTable().ajax.reload();
    	} else {
			initDataTable();
		}
	});

	// Event listener for DataTable draw event
	$('#tb-faktur-list').on('draw.dt', function() {
		$('.loading').hide();
	});

	function initDataTable() {
		table = $('#tb-faktur-list').DataTable({
	        "bProcessing": true,
	        "bServerSide": true,
			"searchDelay": 500,
			"columnDefs": [
				{"title":"No Invoice","targets": 0, },
				{"title":"Tanggal","targets": 1,},
				{"title":"Status","targets": 2,},
				{"title":"No PO","targets": 3,}
			],
	        "sAjaxSource": '<?=base_url()?>ReportBass/GetFakturList',
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
	}

	// Event listener for row click
	$('#tb-faktur-list tbody').on('click', 'td', function() {
		// Get the data of the clicked row
		var rowData = table.row(this).data();

		// Init FakturDTO
		let fakturDTO = new FakturDTO();

		fakturDTO.fakturNo = rowData[0];
		fakturDTO.tglFaktur = rowData[1];
		fakturDTO.status = rowData[2];
		fakturDTO.poNo = rowData[3];

		$("#m-browseFaktur").modal("hide");

		$('#no_faktur').val(fakturDTO.fakturNo);
		
		cariFaktur();
	});

	class FakturDTO {
    	fakturNo = "";
    	tglFaktur = "";
    	status = "";
		poNo = "";
    };
</script>
