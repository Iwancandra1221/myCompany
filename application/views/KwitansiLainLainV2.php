<?php
	if(!isset($_SESSION['logged_in'])){
		redirect('main','refresh');
	}
?>
<style>
	.form-label{
		line-height:2em;
	}
</style>

<div class="container">
	<div class="title">Kwitansi Lain-lain</div>
	
	<div class="row">
	<div class="col-1">
	<label class="form-label">Cabang</label>
	<br>
	<!--div class="btn-group btn-group-toggle" data-toggle="buttons">
		<label class="btn btn-sm btn-info active" onclick="javascript:filter_cancelled('')">
			<input type="radio" name="Jenis" id="option1" autocomplete="off" value="ALL" checked> ACTIVE
		</label>
		<label class="btn btn-sm btn-info" onclick="javascript:filter_cancelled(1)">
			<input type="radio" name="Jenis" id="option2" autocomplete="off" value="CANCELLED" > CANCELLED
		</label>
	</div-->
	</div>
	<div class="col-3">
	<select name="BranchCode" id="branch_code" class="form-control" onchange="javascript:ganti_cabang()" style="min-width:200px">
		<?php
			foreach($db as $d){
				if($_SESSION['logged_in']['branch_id']=='JKT' || $_SESSION['logged_in']['branch_id']==$d['BranchId']){
					$selected = ($_SESSION['logged_in']['branch_id']==$d['BranchId']) ? 'selected' : '';
					echo "<option value='".$d['BranchId']."' ".$selected.">".$d['NamaDb']."</option>";
				}
			}
		?>
	</select>
	</div>
	<div class="col-4">
	</div>
	<div class="col-4 text-right">
	<input type="checkbox" id="chk_cancelled" onclick="javascript:filter_cancelled()"> <label for="chk_cancelled">CANCELLED</label>
	
	<a href='<?php echo site_url('KwitansiLainLainV2/Add') ?>' class='btn btn-dark' style="margin-left:20px">
		<i class='glyphicon glyphicon-plus'></i> Create
	</a>
	</div>
	</div>
	
	<div class="row">
	<div class="col-12">
    <table id="table_kwitansi" class="table table-striped table-bordered" cellspacing="0" width="100%">
			<thead>
			<tr>				
			<th style='width:2%' class='no-sort'>No</th>
			<th style='width:16%'>No Kwitansi</th>
			<th style='width:12%'>Tgl Kwitansi</th>
			<th style='width:*'>Ditransfer Ke</th>
			<th style='width:12%'>Total</th>
			<th style='width:12%'>Modified Date</th>
			<th class='col-hide'>Status</th>
			<th style='width:15%' class='no-sort'><center>Aksi</center></th>
			</tr>
			</thead>
			<tbody>
			<tr>
			<td colspan="8" align="center">Loading...</td>
			</tbody>
	</table>
	-Kwitansi tidak bisa diedit jika sudah ada meterai elektronik.<br>
	-Kwitansi bisa dihapus jika belum ada meterai elektronik, jika sudah ada meterai elektronik, maka dicancel.<br>
	-No kwitansi yang sudah dicancel tidak bisa dipakai kembali
	</div>
	</div>
</div>


<!-- Modal -->
<div class="modal fade" id="modal_view" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<form action="<?php echo site_url('KwitansiLainLainV2/Delete') ?>" method="POST">
				<input type="hidden" name="BranchCode" id="ViewBranchCode" value="">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" style="text-align: center;"><strong>VIEW KWITANSI LAIN-LAIN</strong></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<label class="col-sm-3 col-form-label">No Kwitansi</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewNoKwitansi">
						</div>
					</div>
					<div class="row">
						<label class="col-sm-3 col-form-label">No Bukti</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewNoBukti">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Tgl Kwitansi</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewTglKwitansi">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Telah Terima Dari</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewTerimaDari">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">NPWP</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewNPWP">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Ditransfer Ke</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewDitransferKe">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Email</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewEmail">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Untuk Pembayaran</label>
						<div class="col-sm-9">
							<textarea readonly class="form-control" id="ViewUntukPembayaran"></textarea>
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Total</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewTotal">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Keterangan Internal</label>
						<div class="col-sm-9">
							<textarea readonly class="form-control" id="ViewKeteranganInternal"></textarea>
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Nilai Meterai</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewNilaiMeterai">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">S/N Meterai</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewMeteraiSN">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Status</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewStatus">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">Keterangan Status</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewKeteranganStatus">
						</div>
					</div>
					
					<small>Last Modified on <b><span id="ViewModifiedDate"></span></b> By <b><span id="ViewModifiedBy"></span></b></small>
					
				</div>
				<div class="modal-footer">
					<button id="btnStamping" class="btn btn-danger-dark" type="button" onclick="javascript:Stamping()"><i class="glyphicon glyphicon-qrcode"></i> STAMPING</button>
					<button id="btnLock" class="btn btn-danger-dark" type="button" onclick="javascript:Lock()"><i class="glyphicon glyphicon-lock"></i> LOCK</button>
					<button id="btnUnlock" class="btn btn-danger-dark" type="button" onclick="javascript:Unlock()"><i class="glyphicon glyphicon-lock"></i> UNLOCK</button>
					<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<form action="<?php echo site_url('KwitansiLainLainV2/Delete') ?>" method="POST" id="form_kwitansi_delete">
				<input type="hidden" name="BranchCode" id="BranchCode" value="">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Delete Kwitansi Lain-lain</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">No Kwitansi</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="NoKwitansi" name="NoKwitansi">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">No Bukti</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="NoBukti" name="NoBukti">
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Tgl Kwitansi</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="TglKwitansi">
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Telah Terima Dari</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="TerimaDari">
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">NPWP</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="NPWP">
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Ditransfer Ke</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="DitransferKe">
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Untuk Pembayaran</label>
						<div class="col-sm-9">
							<textarea readonly class="form-control" id="UntukPembayaran"></textarea>
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Total</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="Total">
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Keterangan Internal</label>
						<div class="col-sm-9">
							<textarea readonly class="form-control" id="KeteranganInternal"></textarea>
						</div>
					</div>
					
					<div class="form-group row">
						<label class="col-sm-3 col-form-label">Alasan Delete</label>
						<div class="col-sm-9">
							<input type="text" name="DeletedNote" class="form-control" placeholder="Alasan Delete ...(wajib diisi)" required>
						</div>
					</div>
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger-dark">Delete</button>
					<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>


<script type="text/javascript">
	
	let table_kwitansi;
	let branch_code = '<?php echo $_SESSION['logged_in']['branch_id'] ?>';
	
	function View(BranchCode, NoKwitansi, NoBukti, TglKwitansi, TerimaDari, NPWP, DitransferKe, Email, UntukPembayaran, KeteranganInternal, Total, NilaiMeterai, MeteraiSN, Status, KeteranganStatus, ModifiedBy, ModifiedDate, IsLocked){
		$('#ViewBranchCode').val(BranchCode);
		$('#ViewNoKwitansi').val(NoKwitansi);
		$('#ViewNoBukti').val(((NoBukti=='null')?'':NoBukti));
		$('#ViewTglKwitansi').val(date("d-M-Y",strtotime(TglKwitansi)));
		$('#ViewTerimaDari').val(TerimaDari);
		$('#ViewNPWP').val(NPWP);
		$('#ViewDitransferKe').val(DitransferKe);
		$('#ViewEmail').val(Email);
		$('#ViewUntukPembayaran').val(UntukPembayaran.replace(/<BR>/g, '\n'));
		$('#ViewTotal').val(format_currency(parseFloat(Total).toFixed(0)));
		$('#ViewKeteranganInternal').val(KeteranganInternal.replace(/<BR>/g, '\n'));
		$('#ViewNilaiMeterai').val(NilaiMeterai);
		$('#ViewMeteraiSN').val(MeteraiSN);
		$('#ViewStatus').val(Status);
		$('#ViewKeteranganStatus').val(KeteranganStatus);
		$('#ViewModifiedDate').text(ModifiedDate);
		$('#ViewModifiedBy').text(ModifiedBy);
		$('#btnStamping').hide();
		$('#btnLock').hide();
		$('#btnUnlock').hide();
		if(parseInt(NilaiMeterai)>0){
			if(MeteraiSN==''){
				$('#btnStamping').show();
			}
		}
		else{
			if('<?php echo $_SESSION['logged_in']['userLevel'] ?>'!='STAFF'){
				if(IsLocked=='0'){
					$('#btnLock').show();
				}
				else{
					$('#btnUnlock').show();
				}
			}
		}
		$('#modal_view').modal('show');
	}
	
	function DeleteKwitansiLain(BranchCode, NoKwitansi, NoBukti, TglKwitansi, TerimaDari, NPWP, DitransferKe, Email, UntukPembayaran, KeteranganInternal, Total, EMeteraiSN){
		$('#BranchCode').val(BranchCode);
		$('#NoKwitansi').val(NoKwitansi);
		$('#NoBukti').val(((NoBukti=='null')?'':NoBukti));
		$('#TglKwitansi').val(date("d-M-Y",strtotime(TglKwitansi)));
		$('#TerimaDari').val(TerimaDari);
		$('#NPWP').val(NPWP);
		$('#DitransferKe').val(DitransferKe);
		$('#Email').val(Email);
		$('#UntukPembayaran').val(UntukPembayaran.replace(/<BR>/g, '\n'));
		$('#KeteranganInternal').val(KeteranganInternal.replace(/<BR>/g, '\n'));
		$('#Total').val(format_currency(parseFloat(Total).toFixed(0)));
		$('#EMeteraiSN').val(EMeteraiSN);
		$('#modal_delete').modal('show');
	}
	
	function ViewPDF(BranchCode, NoKwitansi){
		window.open( '<?php echo site_url() ?>/KwitansiLainLainV2/ViewPDF?BranchCode='+BranchCode+'&NoKwitansi='+NoKwitansi, '_blank');
	}
	
	function Stamping(){
		if (confirm('Data sudah BENAR dan ingin STAMPING Kwitansi ini?')) {
			var BranchCode = $('#ViewBranchCode').val();
			var NoKwitansi = $('#ViewNoKwitansi').val();
			// alert(NoKwitansi);
			var StampingURL = '<?php echo base_url() ?>KwitansiLainLainV2/Stamping?BranchCode='+BranchCode+'&NoKwitansi='+NoKwitansi;
			//alert(StampingURL);

			$('.loading').show();
			$.ajax({
				url			: StampingURL,
				cache		: false,
				type		: 'GET',
				dataType  	: 'json',
				success   	: function(res) {
					// console.log(res);
					$('.loading').hide();
					if(res.msg=='SUCCESS'){
						$('#modal_view').modal('hide');
						alert('Kwitansi berhasil di-STAMPING');
						table_kwitansi.ajax.reload();
					}
					else{
						alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
					}
				}
			});
		}	
	}
	
	function Lock(){
		if (confirm('Data sudah BENAR dan ingin LOCK Kwitansi ini?')) {
			var BranchCode = $('#ViewBranchCode').val();
			var NoKwitansi = $('#ViewNoKwitansi').val();
			// alert(NoKwitansi);
			var LockURL = '<?php echo base_url() ?>KwitansiLainLainV2/Lock?BranchCode='+BranchCode+'&NoKwitansi='+NoKwitansi;

			$('.loading').show();
			$.ajax({
				url			: LockURL,
				cache		: false,
				type		: 'GET',
				// dataType  	: 'json',
				success   	: function(res) {
					console.log(res);
					$('.loading').hide();
					if(res.msg=='SUCCESS'){
						$('#modal_view').modal('hide');
						alert('Kwitansi berhasil di-LOCK');
						table_kwitansi.ajax.reload();
					}
					else{
						alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
					}
				}
			});
		}	
	}
	
	function Unlock(){
		if (confirm('Apakah ingin UNLOCK Kwitansi ini?')) {
			var BranchCode = $('#ViewBranchCode').val();
			var NoKwitansi = $('#ViewNoKwitansi').val();
			// alert(NoKwitansi);
			var LockURL = '<?php echo base_url() ?>KwitansiLainLainV2/Unlock?BranchCode='+BranchCode+'&NoKwitansi='+NoKwitansi;

			$('.loading').show();
			$.ajax({
				url			: LockURL,
				cache		: false,
				type		: 'GET',
				dataType  	: 'json',
				success   	: function(res) {
					// console.log(res);
					$('.loading').hide();
					if(res.msg=='SUCCESS'){
						$('#modal_view').modal('hide');
						alert('Kwitansi berhasil di-UNLOCK');
						table_kwitansi.ajax.reload();
					}
					else{
						alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
					}
				}
			});
		}	
	}
	
	function Uncancelled(BranchCode, NoKwitansi){
		if (confirm('Ingin UNCANCELLED Kwitansi ini?')) {
			$('.loading').show();
			$.ajax({
				url			: '<?php echo base_url() ?>KwitansiLainLainV2/Uncancelled?BranchCode='+BranchCode+'&NoKwitansi='+NoKwitansi,
				cache		: false,
				type		: 'GET',
				dataType  	: 'json',
				success   	: function(res) {
					$('.loading').hide();
					if(res.msg=='SUCCESS'){
						alert('Kwitansi berhasil di-UNCANCELLED');
						table_kwitansi.ajax.reload();
					}
					else{
						alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
					}
				}
			});
		}	
	}
	
	function filter_cancelled(){
		table_kwitansi.ajax.reload();
	}
	
	function ganti_cabang(){
		branch_code = $('#branch_code').val();
		table_kwitansi.ajax.reload();
	}
	
	function format_currency(x){
		var str = x.replace(/[^0-9.]/g,'');
		x = str.split('.'); 
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : ''; 
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		} 
		return x1 + x2;
	}
	
	$(document).ready(function() {
		$("#form_kwitansi_delete").submit(function() {
			var act = $(this).attr('action');
			var form_data = new FormData(this);
				$('.loading').show();
				$.ajax({
					data      	: form_data,
					url			: act,
					cache		: false,
					contentType	: false,
					processData	: false,
					type		: 'POST',
					dataType  	: 'json',
					success   	: function(res) {
						// console.log(res);
						$('.loading').hide();
						if(res.msg=='SUCCESS'){
							alert('Kwitansi berhasil didelete!');
							window.location.href='<?php echo site_url('KwitansiLainLainV2') ?>';
						}
						else{
							alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
						}
					}
				});
			event.preventDefault();
		});
		
		table_kwitansi = $('#table_kwitansi').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"lengthChange"  : false,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"order": [[2, 'desc'], [ 1, "desc" ]],
			"autoWidth": false,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": '<?php echo base_url('KwitansiLainLainV2/datatable_kwitansi') ?>',
				"type": "GET",
				"datatype": "json",
				"data": function (data) {
					data.userLevel = '<?php echo $_SESSION['logged_in']['userLevel'] ?>';
					data.branch_code = branch_code;
					data.is_cancelled = Number($('#chk_cancelled').prop('checked'));
				},
				 "error": function (xhr, error, code) {
					// console.log(xhr, code);
					alert(  '['+error+'] server tidak bisa dihubungi atau error data.' );
				}
			},
			"initComplete": function() {
				$('#table_kwitansi_filter input').unbind();
				$('#table_kwitansi_filter input').bind('keyup', function(e) {
					if(e.keyCode == 13) {
						table_kwitansi.search(this.value).draw();   
					}
				}); 
			},
		});
		
	});
		
</script>


