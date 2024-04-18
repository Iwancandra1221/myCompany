<?php
	if(!isset($_SESSION['logged_in'])){
		redirect('main','refresh');
	}
?>
<style>
</style>
<script>
	var t;
	$(document).ready(function() {
	    t = $('#tabel_kwitansi_lainnya').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide',  visible: false }
			],
			// "dom": '<"top"l>rt<"bottom"ip><"clear">',
			"order": [ [ 5, 'desc' ]]
		});
		
		t.on('order.dt search.dt', function () {
			let i = 1;
			t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$("#tabel_kwitansi_lainnya").dataTable().fnFilter("^"+''+"$", 6, true); //filter active saja di awal load
	});
</script>

<div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;">
	<div style="padding: 5px;">
		<?php
			if ($this->session->flashdata('success')) {
				echo "<div class='alert alert-success' id='flash-msg' style='float:auto'>".$this->session->flashdata('success')."</div>";
			}
			if ($this->session->flashdata('error')) {
				echo "<div class='alert alert-danger' id='flash-msg' style='float:auto'>".$this->session->flashdata('error')."</div>";
			}
		?>
	</div>
</div>
<!-- Fixed navbar -->

<div class="container">
	<div class="title">Kwitansi Lain-lain</div>
	<div class="btn-group btn-group-toggle" data-toggle="buttons">
		<label class="btn btn-sm btn-info active" onclick="javascript:filter_cancelled('')">
			<input type="radio" name="Jenis" id="option1" autocomplete="off" value="ALL" checked> ACTIVE
		</label>
		<label class="btn btn-sm btn-info" onclick="javascript:filter_cancelled(1)">
			<input type="radio" name="Jenis" id="option2" autocomplete="off" value="CANCELLED" > CANCELLED
		</label>
	</div>
	
	<a href='<?php echo site_url('KwitansiLainLain/Add') ?>' class='btn btn-dark' style='float:right; margin-bottom:5px'>
		<i class='glyphicon glyphicon-plus'></i> Create
	</a>
    <table id="tabel_kwitansi_lainnya" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php
			echo "<thead>";
			echo "<tr>";				
			echo "<th style='width:2%' class='no-sort'>No</th>";
			echo "<th style='width:15%'>No Kwitansi</th>";
			echo "<th style='width:12%'>Tgl Kwitansi</th>";
			echo "<th style='width:*'>Ditransfer Ke</th>";
			echo "<th style='width:15%'>Total</th>";
			echo "<th style='width:12%'>Modified Date</th>";
			echo "<th class='col-hide'>Status</th>";
			echo "<th style='width:15%' class='no-sort'><center>Aksi</center></th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->NoKwitansi."</td>"; 
				echo "<td>".date('d-M-Y', strtotime($r->TglKwitansi))."</td>"; 
				echo "<td>".$r->DitransferKe."</td>";
				echo "<td align='right'>".number_format($r->Total)."</td>";
				echo "<td>".date('d-M-Y H:i:s', strtotime($r->ModifiedDate))."</td>";
				echo "<td>".$r->IsCancelled."</td>";
				echo "<td>";
				echo "<center>";
				
				if($r->IsCancelled==1){
					if($_SESSION['logged_in']['userLevel']!='STAFF' && $_SESSION['logged_in']['userLevel']!='SPG'){
					echo '<button class="btn btn-sm  btn-danger-dark" title="UNCANCELLED" onclick="Uncancelled(\''.$r->NoKwitansi.'\')"><i class="glyphicon glyphicon-repeat"></i></button>';
					}
				}
				else{
					if($r->EMeteraiSN!=''){
						echo "<button class='btn btn-sm btn-dark' title='EDIT' style='cursor: not-allowed' disabled><i class='glyphicon glyphicon-pencil'></i></button>";
					}
					else{
						echo "
						<form action='".site_url('KwitansiLainLain/Edit')."' method='POST' style='display:inline'>
						<input type='hidden' name='NoKwitansi' value='".$r->NoKwitansi."'>
						<button type='submit' title='EDIT' class='btn btn-sm btn-dark'><i class='glyphicon glyphicon-pencil'></i></button>
						</form>";
					}
					echo '
					<button class="btn btn-sm  btn-danger-dark" title="DELETE" onclick="DeleteKwitansiLain(\''.$r->NoKwitansi.'\',\''.date('d-M-Y', strtotime($r->TglKwitansi)).'\',\''.$r->DitransferKe.'\',\''.$r->UntukPembayaran.'\',\''.$r->KeteranganInternal.'\',\''.number_format($r->Total).'\')"><i class="glyphicon glyphicon-trash"></i></button>';
					
				}
				
				echo ' <button class="btn btn-sm btn-dark" title="VIEW" onclick="View(
				\''.$r->NoKwitansi.'\',
				\''.date('d-M-Y', strtotime($r->TglKwitansi)).'\',
				\''.$r->TerimaDari.'\',
				\''.$r->NPWP.'\',
				\''.$r->DitransferKe.'\',
				\''.$r->Email.'\',
				\''.$r->UntukPembayaran.'\',
				\''.number_format($r->Total).'\',
				\''.$r->KeteranganInternal.'\',
				\''.$r->EMeteraiSN.'\',
				\''.(($r->IsCancelled==1) ? "CANCELLED" : "ACTIVE").'\',
				\''.$r->CancelledNote.'\',
				\''.$r->ModifiedBy.'\',
				\''.date('d-M-Y H:i:s', strtotime($r->ModifiedDate)).'\')">
				<i class="glyphicon glyphicon-search"></i></button>';
				
				//SUPAYA TIDAK KELIHATAN URL NO KWITANSI, MAKA PAKAI POST
				echo "
					<form action='".site_url('KwitansiLainLain/ViewPDF')."' method='POST' target='_blank' style='display:inline'>
					<input type='hidden' name='NoKwitansi' value='".$r->NoKwitansi."'>
					<button type='submit' class='btn btn-sm btn-dark' title='VIEW PDF'><i class='glyphicon glyphicon-file'></i></button>
					</form>";
				echo "</center>";
				echo "</td>";
				echo "</tr>";
				$i += 1;
			}
			echo "</tbody>";
		?>
	</table>
	-Kwitansi bisa dihapus jika belum ada meterai elektronik, jika sudah ada meterai elektronik, maka dicancel.<br>
	-Kwitansi tidak bisa diedit jika sudah ada meterai elektronik.<br>
	-No kwitansi yang sudah batal tidak bisa dipakai kembali
</div>


<!-- Modal -->
<div class="modal fade" id="modal_view" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<form action="<?php echo site_url('KwitansiLainLain/Delete') ?>" method="POST">
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
							<input type="text" readonly class="form-control" id="ViewUntukPembayaran">
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
							<input type="text" readonly class="form-control" id="ViewKeteranganInternal">
						</div>
					</div>
					
					<div class="row">
						<label class="col-sm-3 col-form-label">S/N Meterai</label>
						<div class="col-sm-9">
							<input type="text" readonly class="form-control" id="ViewMeterai">
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
					
					Last Modified on <b><span id="ViewModifiedDate"></span></b> By <b><span id="ViewModifiedBy"></span></b>
					
				</div>
				<div class="modal-footer">
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
			<form action="<?php echo site_url('KwitansiLainLain/Delete') ?>" method="POST" id="form_kwitansi_delete">
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
							<input type="text" readonly class="form-control" id="UntukPembayaran">
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
							<input type="text" readonly class="form-control" id="KeteranganInternal">
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
	
	function View(NoKwitansi, TglKwitansi, TerimaDari, NPWP, DitransferKe, Email, UntukPembayaran, Total, KeteranganInternal, Meterai, Status, KeteranganStatus, ModifiedBy, ModifiedDate){
		$('#ViewNoKwitansi').val(NoKwitansi);
		$('#ViewTglKwitansi').val(TglKwitansi);
		$('#ViewTerimaDari').val(TerimaDari);
		$('#ViewNPWP').val(NPWP);
		$('#ViewDitransferKe').val(DitransferKe);
		$('#ViewEmail').val(Email);
		$('#ViewUntukPembayaran').val(UntukPembayaran);
		$('#ViewTotal').val(Total);
		$('#ViewKeteranganInternal').val(KeteranganInternal);
		$('#ViewMeterai').val(Meterai);
		$('#ViewStatus').val(Status);
		$('#ViewKeteranganStatus').val(KeteranganStatus);
		$('#ViewModifiedDate').text(ModifiedDate);
		$('#ViewModifiedBy').text(ModifiedBy);
		$('#modal_view').modal('show');
	}
	
	function DeleteKwitansiLain(NoKwitansi, TglKwitansi, DitransferKe, UntukPembayaran, KeteranganInternal, Total){
		$('#NoKwitansi').val(NoKwitansi);
		$('#TglKwitansi').val(TglKwitansi);
		$('#DitransferKe').val(DitransferKe);
		$('#UntukPembayaran').val(UntukPembayaran);
		$('#KeteranganInternal').val(KeteranganInternal);
		$('#Total').val(Total);
		$('#modal_delete').modal('show');
	}
	
	function ViewPDF(NoKwitansi){
		window.open( '<?php echo site_url() ?>/KwitansiLainLain/ViewPDF?NoKwitansi='+NoKwitansi, '_blank');
	}
	
	function Uncancelled(NoKwitansi){
		if (confirm('Ingin UNCANCELLED Kwitansi ini?')) {
			$('.loading').show();
			$.ajax({
				url			: '<?php echo site_url() ?>/KwitansiLainLain/Uncancelled?NoKwitansi='+NoKwitansi,
				cache		: false,
				type		: 'GET',
				dataType  	: 'json',
				success   	: function(res) {
					$('.loading').hide();
					if(res.msg=='SUCCESS'){
						alert('Kwitansi berhasil di-UNCANCELLED');
						window.location.href='<?php echo site_url('KwitansiLainLain') ?>';
					}
					else{
						alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
					}
				}
			});
		}	
	}
	
	function filter_cancelled(status){
		$("#tabel_kwitansi_lainnya").dataTable().fnFilter("^"+status+"$", 6, true); 
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
						$('.loading').hide();
						if(res.msg=='SUCCESS'){
							alert('Kwitansi berhasil didelete');
							window.location.href='<?php echo site_url('KwitansiLainLain') ?>';
						}
						else{
							alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
						}
					}
				});
			event.preventDefault();
		});
	});
		
</script>


