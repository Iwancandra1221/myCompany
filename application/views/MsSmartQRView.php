<?php
	if(!isset($_SESSION['logged_in'])){
		redirect('main','refresh');
	}
?>
<style>
</style>
<script>
</script>

<!--div style="position:absolute; top:0; left:0; width: 100%; z-index: 99999 !important;padding: 5px;"-->
<div id="notifikasi">
	<?php
		if(ISSET($msg)) {
			if($msg == 'success') {
				echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-ok-sign"></i>
					'.$description.'
				</div>';
			}
			if($msg == 'failed') {
				echo '
				<div class="msg msg-danger">
					<i class="glyphicon glyphicon-remove-circle"></i>
					'.$description.'
				</div>';
			}
		}
	?>
</div>
<!-- Fixed navbar -->

<div class="container">
	<div class="title">QR CODE & LANDING PAGE</div>
	
	<div class="row" style="margin-bottom:10px">
		<div class="col-md-9">
			Merk
			<select id="filter_merk" class="form-control-dark" onchange="javascript:filter_merk()">
				<option value="">ALL</option>
				<?php
					foreach($merks as $merk){
						echo "<option value='".$merk."'>".$merk."</option>";
					}
				?>
			</select>
		</div>
		<div class="col-md-3" style="text-align:right">
			<div class="input-group input-group-dark">
				<span class="input-group-addon">
					<i class="glyphicon glyphicon-search"></i>
				</span>
			<input type="text" class="form-control form-control-dark" id="cari" placeholder="Search ...">
			</div>
		</div>
	</div>
	
	
    <table id="table-master" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<?php 
			echo "<thead>";
			echo "<tr>";
			echo "<th style='width:2%' class='no-sort'>No</th>";
			echo "<th style='width:10%'>MERK</th>";
			echo "<th style='width:10%'>TIPE BARANG</th>";
			echo "<th style='width:12%'>LOKASI QR CODE</th>";
			echo "<th style='width:15%'>GRUP/TIPE BRG</th>";
			echo "<th style='width:10%'>CREATE DATE</th>";
			echo "<th>URL LANDING PAGE</th>";
			echo "<th class='col-hide' style='width:0'>URL LANDING PAGE</th>";
			echo "<th class='col-hide' style='width:0'>URL</th>";
			echo "<th style='width:5%' class='no-sort'>AKSI</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i = 1;
			foreach($result as $r) {
				echo "<tr>"; 
				echo "<td>".$i."</td>";
				echo "<td>".$r->merk."</td>"; 
				echo "<td>".$r->tipe."</td>"; 
				echo "<td>".$r->lokasi_qr_code."</td>";
				echo "<td>".(($r->isgroup==1) ? 'GROUP TIPE BARANG' : 'TIPE BARANG')."</td>";
				echo "<td>".date('d-M-Y', strtotime($r->created_date))."</td>";
				echo '<td>
						<div class="input-group">
						<input type="text" id="url_'.$r->id.'" class="url_redirect form-control form-control-no-border" value="'.$r->url_redirect.'" disabled>
							<span class="input-group-addon" onclick="javascript:editURLLandingPage('.$r->id.')" style="cursor:pointer">
								<i id="btn_edit_url_'.$r->id.'" class="glyphicon glyphicon-edit" style="color:blue"></i>
							</span>
						</div>
				</td>';
				echo "<td>".$r->url_redirect."</td>";
				echo "<td>".$r->url."</td>";
				echo '<td>
				<div class="btn-group" role="group" aria-label="...">
				<button type="button" class="btn btn-sm btn-default" onclick="javascript:viewSmartQR('.$r->id.')"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				</td>'; 
				echo "</tr>";
				$i += 1;
			}
		echo "</tbody>"; ?>
	</table>
</div>	


<div id="modal_view" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true" style="background:red; padding:0px 10px">&times;</span>
				</button>
				<h4 class="modal-title"  style="text-align: center;"> <strong>VIEW DETAIL MASTER QR CODE</strong> </h4>
			</div>
			<div class="modal-body p30">
				<?php echo form_open('MsSmartQR/SmartQRViewUpdate',array('class'=>'form-horizontal','id' => 'form_edit')); ?>
					<div class="form-group">
						<label class="col-xs-3"></label>
						<div class="col-xs-3" id="div_qrcode">
							<img height="180px" id="view_qrcode">
						</div>
						<div class="col-xs-4">
							<button type="button" class="btn btn-dark w200px mb10 btnQRCode" onclick="javascript:copyURL()">
								<i class="glyphicon glyphicon-copy"></i> COPY URL
							</button>
							<br>
							<a href="#" id="btnDownload">
								<button type="button" class="btn btn-dark w200px mb10 btnQRCode">
									<i class="glyphicon glyphicon-download"></i> DOWNLOAD
								</button>
							</a>
							<br>
							<button type="button" class="btn btn-dark w200px mb10 btnQRCode" onclick="javascript:printQRCode()">
								<i class="glyphicon glyphicon-print"></i> PRINT
							</button>
							<br>
							<button type="button" class="btn btn-dark w200px btnQRCode" onclick="javascript:openURL()">
								<i class="glyphicon glyphicon-globe"></i> TEST URL
							</button>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Merk</label>
						<div class="col-xs-9">
							<input type="hidden" name="id" id="view_id">
							<input type="hidden" id="view_url">
							<input type="text" id="view_merk" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Type Barang</label>
						<div class="col-xs-9">
							<input type="text" id="view_tipe" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-3">Lokasi QR Code</label>
						<div class="col-xs-4">
							<input type="text" id="view_lokasi_qr_code" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
						<div class="col-xs-1 view_qty">
							<input type="text" id="view_qty" class="form-control form-control-dark" style="background:#d3d3d3" disabled>
						</div>
						<label class="col-xs-3 view_qty" id="view_qty_lokasi">QTY PER </label>
					</div>
					<div class="form-group">
						<label class="col-xs-3">URL Landing Page</label>
						<div class="col-xs-9">
							
							<div class="input-group">
							<input type="text" name="url_redirect" id="view_url_redirect" class="form-control" disabled>
								<span class="input-group-addon" onclick="javascript:editViewURLLandingPage()" style="cursor:pointer">
									<i id="btn_edit_url" class="glyphicon glyphicon-edit" style="color:blue"></i>
								</span>
							</div>
						</div>
					</div>
					<div id="view_param">
					</div>
					<div class="form-group">
						<label class="col-xs-3"></label>
						<div class="col-xs-7">
							<small>
							<span id="view_created"></span><br>
							<span id="view_modified"></span>
							</small>
						</div>
						<div class="col-xs-2">
							<button type="button" class="btn btn-danger-dark" onclick="javascript:willDeleteSmartQR()"><i class="glyphicon glyphicon-trash"></i> HAPUS</button>
						</div>
					</div>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content" >
				<?php echo form_open('MsSmartQR/SmartQRDelete',array('id' => 'form_delete')); ?>
				<div class="modal-body"  style="text-align: center;"> 
					<i class="glyphicon glyphicon-exclamation-sign" style="font-size:400%;color:red"></i>
					<br>
					<p style="font-size:120%;color:red;font-weight:bold">APAKAH ANDA YAKIN INGIN MENGHAPUS MASTER QR CODE INI ?</p>
					<p style="font-size:120%;color:red;font-weight:bold">ALASAN MENGHAPUS ?</p>
					<input type="hidden" name="id" id="delete_id">
					<input type="hidden" name="tipe" id="delete_tipe">
					<input type="hidden" name="merk" id="delete_merk">
					<input type="hidden" name="lokasi_qr_code" id="delete_lokasi_qr_code">
					<input type="text" name="reason_deleted" id="reason_deleted" class="form-control form-control-dark" onkeypress="return event.keyCode!=13" required>
					<br>
					<button type="submit" id="btn_delete" class="btn btn-danger-dark" onclick="javascript:deleteSmartQRXXX()" >YA, HAPUS</button>
					<button type="button" class="btn btn-dark" data-dismiss="modal">CANCEL</button> 
				</div>
				<div class="modal-footer">
				</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">	
	
	var tableMaster;
	$(document).ready(function() {
	
	    tableMaster = $('#table-master').DataTable({
			"pageLength"    : 10,
			"searching"     : true,
			"columnDefs": [
			{ targets: 'no-sort', orderable: false },
			{ targets: 'col-hide', visible: false }
			],
			"dom": '<"top"l>rt<"bottom"ip><"clear">',
			"order": [[7, 'asc'], [1, 'asc'], [2, 'asc'], [3, 'asc']], //order = url_landing_page, merk, tipe, lokasi
			"language": {
				"paginate": {
				  "previous": "<",
				  "next": ">"
				}
			  },
		});
		
		$("<a href='<?php echo site_url('MsSmartQR/SmartQRAdd') ?>' class='btn btn-dark' style='float:right; margin-bottom:5px'><i class='glyphicon glyphicon-plus'></i> CREATE NEW</a>").insertBefore('#table-master');
		
		tableMaster.on('order.dt search.dt', function () {
			let i = 1;
			tableMaster.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		$('#cari').keyup(function(){
			tableMaster.search($(this).val()).draw();
			// search_qrcode($(this).val());
		})
		
		$('.url_redirect').keyup(function(event){
			if (event.key === "Enter"){
				let x = this.id.split('_');
				var url_redirect = $('#'+this.id).val();
				updateURLRedirect(x[1],url_redirect);
			}
		});
		
		$(".msg").delay(3000).fadeOut("slow");
		
	});
	
	function viewSmartQR(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsSmartQR/GetList?id=") ?>'+id,
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				// alert(data);
				if(data.id>0){
					$('#view_id').val(data.id);
					$('#view_merk').val(data.merk);
					$('#view_tipe').val(data.tipe);
					$('#view_url').val(data.url);
					$('#view_url_redirect').val(data.url_redirect);
					$('#view_lokasi_qr_code').val(data.lokasi_qr_code);
					
					let qty = parseInt(data.qty) || 0;
					if(qty>0){
						$('#view_qty').val(qty);
						$('#view_qty_lokasi').text('QTY PER '+data.lokasi_qr_code);
						$('.view_qty').show();
					}
					else{
						$('.view_qty').hide();
					}
					
					$('#view_created').text('Created on '+data.created_tgl+' | '+data.created_jam+' | '+data.created_by);
					$('#view_modified').text('Last Modified on '+data.modified_tgl+' | '+data.modified_jam+' | '+data.modified_by);
					$('#view_qrcode').attr("src", 'data:image/png;base64,' +data.qrcode);
					$('#btnDownload').attr("href", 'data:image/png;base64,' +data.qrcode);
					$('#btnDownload').attr("download", data.filename);
					
					if(data.url_redirect==null || data.url_redirect==''){
						$('.btnQRCode').prop('disabled', true);
					}
					else{
						$('.btnQRCode').prop('disabled', false);
					}
					
					var paramHtml = '';
					for(i=0;i<data.param.length;i++){
						paramHtml += '<div class="form-group">'+
							'<label class="col-3">'+data.param[i]['ParamName']+'</label>'+
							'<div class="col-9">'+
								'<input type="text" class="form-control form-control-dark" value="'+data.param[i]['ParamValue']+'" style="background:#d3d3d3" disabled>'+
							'</div>'+
						'</div>';
					}
					$('#view_param').html(paramHtml);
					$('#modal_view').modal('show');
				}
			}
		});
	}
	
	function editSmartQR(id){
		$('.loading').show();
		$.ajax({ 
			type: 'GET', 
			url: '<?php echo site_url("MsSmartQR/GetList?id=") ?>'+id,
			dataType: 'json',
			success: function (data){
				$('.loading').hide();
				// alert(data);
				if(data.id>0){
					$('#edit_id').val(data.id);
					$('#edit_merk').val(data.merk);
					$('#edit_tipe').val(data.tipe);
					$('#edit_lokasi_qr_code').val(data.lokasi_qr_code);
					$('#edit_url').val(data.url);
					$('#edit_url_redirect').val(data.url_redirect);
					$('#modal_edit').modal('show');
				}
			}
		});
	}
	
	function editURLLandingPage(id){
		var cur_disabled = $('#url_'+id).is(':disabled');
		// alert(cur_disabled);
		$('#url_'+id).prop('disabled', !cur_disabled);
		
		if(cur_disabled==true){
			$('#btn_edit_url_'+id).removeClass('glyphicon-edit');
			$('#btn_edit_url_'+id).addClass('glyphicon-floppy-disk');
			$('#url_'+id).select();
			currentURL =  $('#url_'+id).val();
		}
		else{
			$('#btn_edit_url_'+id).removeClass('glyphicon-floppy-disk');
			$('#btn_edit_url_'+id).addClass('glyphicon-edit');
			var url_redirect = $('#url_'+id).val();
			updateURLRedirect(id,url_redirect);
		}
	}
	
	function editViewURLLandingPage(){
		var id = $('#view_id').val();
		var url_redirect = $('#view_url_redirect').val();
		
		var cur_disabled = $('#view_url_redirect').is(':disabled');
		
		if(cur_disabled==true){
			$('#view_url_redirect').prop('disabled', !cur_disabled);
			$('#btn_edit_url').removeClass('glyphicon-edit');
			$('#btn_edit_url').addClass('glyphicon-floppy-disk');
			$('#view_url_redirect').select();
		}
		else{
			$('#btn_edit_url').removeClass('glyphicon-floppy-disk');
			$('#btn_edit_url').addClass('glyphicon-edit');
			// updateURLRedirect(id,url_redirect);
			$('#form_edit').submit();
		}
	}
	
	function willDeleteSmartQR(){
		var id = $('#view_id').val();
		var tipe = $('#view_tipe').val();
		var merk = $('#view_merk').val();
		var lokasi_qr_code = $('#view_lokasi_qr_code').val();
		// alert(id);
		$('#delete_id').val(id);
		$('#delete_tipe').val(tipe);
		$('#delete_merk').val(merk);
		$('#delete_lokasi_qr_code').val(lokasi_qr_code);
		$('#modal_delete').modal('show');
	}
	
	function deleteSmartQR(){
		var id = $('#view_id').val();
		var reason_deleted = $('#reason_deleted').val();
		var tipe = $('#view_tipe').val();
		var merk = $('#view_merk').val();
		var lokasi_qr_code = $('#view_lokasi_qr_code').val();
		if(reason_deleted==''){
			alert('Alasan menghapus wajib diisi!');
			$('#reason_deleted').focus();
			return false;
		}
		else{
			$('.loading').show();
			$('#modal_delete').modal('hide');
			$.ajax({ 
				type: 'POST', 
				url: '<?php echo site_url("MsSmartQR/SmartQRDelete") ?>', 
				data: { id: id, tipe: tipe, merk: merk, lokasi_qr_code: lokasi_qr_code, reason_deleted: reason_deleted }, 
				dataType: 'json',
				success: function (data) {
					$('.loading').hide();
					// alert(data.result+'\n'+data.message);
					if(data.result=='SUKSES'){
						location.reload();
					}
				}
			});
		}
	}
	
	function copyURL() {
		let url = document.getElementById("view_url").value;
		
		// ----- hanya bisa di https -----
		// navigator.clipboard.writeText(url);
		// alert("URL QRCode Berhasil di-copy:\n" + url);
		// -------------------------------
		// if(navigator.clipboard.writeText(url)){
			// alert("URL QRCode Berhasil di-copy:\n" + url);
		// }
		// else{
			// prompt("Tekan Ctrl+C untuk copy url ", url);
		// }
		prompt("Tekan Ctrl+C untuk copy url ", url);
	}
	
	function openURL() {
		var url = document.getElementById("view_url").value;
		window.open(url, '_blank');
	}
	
	function filter_merk() {
		var merk = $('#filter_merk').val();
		if(merk==''){
			$("#table-master").dataTable().fnFilter(merk, 1);
		}
		else{
			$("#table-master").dataTable().fnFilter("^"+merk+"$", 1, true);
		}
	}
	
	function search_qrcode(qrcode){
		var data = t.rows().data();
		data.each(function (value, index) {
			// console.log(`For index ${index}, data value is ${value}`);
			// console.log('value5='+value[5].replace(/&amp;/g, '&'));
			// console.log('qrcode='+qrcode);
			if(value[5].replace(/&amp;/g, '&')==qrcode) { //5 = kolom URL
				alert('QRCode ditemukan!');
			}
		});
	}
		
	function updateURLRedirect(id, url_redirect) {
		$('#modal_view').modal('hide');
		$('.loading').show();
		$.ajax({ 
			type: 'POST', 
			url: '<?php echo site_url("MsSmartQR/SmartQRUpdate") ?>', 
			data: { id:id, url_redirect: url_redirect }, 
			dataType: 'json',
			success: function (data) {
				$('.loading').hide();
				if(data.result=='SUKSES'){
					var msg = '<div class="msg msg-danger">'+
									'<i class="glyphicon glyphicon-ok-sign"></i>'+data.message+
								'</div>';
					$('#notifikasi').html(msg);
					location.reload();
				}
			}
		});
	}
	
	function printQRCode(){
		var divToPrint=document.getElementById('div_qrcode');
		var newWin=window.open('','Print-Window');
		newWin.document.open();
		newWin.document.write('<html><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
		newWin.document.close();
		setTimeout(function(){newWin.close();},10);
	}
	
</script>
