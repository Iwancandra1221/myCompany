<!--link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script-->

<style>	
	.form-control[readonly]{
	/*background:#ddd;*/
	}
	
	.dropdown-menu {
		z-index: 1031;
	}
</style>
<div class="container">
	<div class="form_title">
		<a href="<?php echo base_url('KwitansiLainLainV2') ?>" class="float-left">
			<i class="glyphicon glyphicon-dark glyphicon-circle-arrow-left" style="font-size:200%"></i>
		</a>
		<div style="text-align: center;"><?php echo (ISSET($result->NoKwitansi) ? "EDIT" : "CREATE") ?> KWITANSI LAIN-LAIN</div>
	</div>
	<br>
	<?php
		echo form_open('KwitansiLainLainV2/Save',array('id' => 'form_kwitansi_add'));
	?>
	<div class="border20 p20">
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Cabang</big><br><small><em>Required</em></small></div>
			<div class="col-3 col-m-4">
				<select name="BranchCode" id="branch_code" class="form-control" onchange="javascript:ganti_cabang()" <?php echo (ISSET($result->NoKwitansi) ? "disabled" : "") ?>>
					<?php
						// $selected = $_SESSION['logged_in']['branch_id'];
						// $selected = (ISSET($result->BranchCode)) ? $result->BranchCode : $selected;
						// foreach($db as $d){
							// $s = ($selected==$d->BranchId) ? 'selected' : '';
							// echo "<option value='".$d->BranchId."' ".$s.">".$d->NamaDb."</option>";
						// }
						
						
						// $selected = ($_SESSION['logged_in']['branch_id']=='JKT') ? 'JKT' : $_SESSION['logged_in']['branch_id'];
						$selected = $_SESSION['logged_in']['branch_id'];
						$selected = (ISSET($result->BranchCode)) ? $result->BranchCode : $selected;
					
						foreach($db as $d){
							if($_SESSION['logged_in']['branch_id']=='JKT' || $_SESSION['logged_in']['branch_id']==$d['BranchId']){
								$s = ($selected==$d['BranchId']) ? 'selected' : '';
								echo "<option value='".$d['BranchId']."' ".$s.">".$d['NamaDb']."</option>";
							}
						}
						
						
					?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><big>No. Kwitansi</big><br><small><em>Required</em></small></div>
			<div class="col-3 col-m-2">
				<!-- SIMPAN WILAYAH DARI DEALER/SUPPLIER UNTUK STAMPING -->
				<input type="hidden" class="form-control" name="Wilayah" id="Wilayah"
				value="<?php echo (ISSET($result->Wilayah) ? $result->Wilayah : "") ?>"
				>
				<input type="text" class="form-control" name="NoKwitansi" id="NoKwitansi"
				value="<?php echo (ISSET($result->NoKwitansi) ? $result->NoKwitansi : "") ?>"
				<?php echo (ISSET($result->NoKwitansi) ? "readonly" : "") ?>
				required>
			</div>
			
			<div class="col-6 col-m-6">
				<div class="input-group">
					<input type="text" name="NoBukti" id="NoBukti" class="form-control"
					value="<?php echo (ISSET($result->NoBukti) ? $result->NoBukti : "") ?>" onkeydown="return false"  autocomplete="off">
					<span class="input-group-btn">
						<button id="btnBrowseBukti" class="btn btn-default" type="button" onclick="javascript:ShowModalBukti()" >Pilih BBT</button>
					</span>
				</div><!-- /input-group -->
			</div>
			
			
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><big>Tgl. Kwitansi</big><br><small><em>Required</em></small></div>
			<div class="col-3 col-m-2">
				<div class="input-group">
					<span class="input-group-addon" id="dtbtn">
						<i class="glyphicon glyphicon-calendar"></i>
					</span>
					<input type="text" name="TglKwitansi" id="TglKwitansi" class="form-control dtpicker" value="<?php echo (ISSET($result->TglKwitansi) ? date('d-M-Y', strtotime($result->TglKwitansi)) : date('d-M-Y'))  ?>" readonly>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-2"></div>
			<div class="col-m-10">				
				<div class="btn-group btn-group-toggle" data-toggle="buttons">
					<label class="btn btn-info <?php echo (ISSET($result->Jenis) && $result->Jenis=="REKENING BANK" ? "active" : "") ?>" onclick="javascript:Browse('1')">
						<input type="radio" name="Jenis" id="option1" autocomplete="off" value="REKENING BANK"
						<?php echo (ISSET($result->Jenis) && $result->Jenis=="REKENING BANK" ? "checked" : "") ?> required> REKENING BANK
					</label>
					<label class="btn btn-info <?php echo (ISSET($result->Jenis) && $result->Jenis=="REKENING VA" ? "active" : "") ?>" onclick="javascript:Browse('2')">
						<input type="radio" name="Jenis" id="option2" autocomplete="off" value="REKENING VA"
						<?php echo (ISSET($result->Jenis) && $result->Jenis=="REKENING VA" ? "checked" : "") ?> required> REKENING VA
					</label>
					<label class="btn btn-info <?php echo (ISSET($result->Jenis) && $result->Jenis=="OTHER" ? "active" : "") ?>" onclick="javascript:Browse('3')">
						<input type="radio" name="Jenis" id="option3" autocomplete="off" value="OTHER"
						<?php echo (ISSET($result->Jenis) && $result->Jenis=="OTHER" ? "checked" : "") ?> required> OTHER
					</label>
				</div>
			</div>
		</div>
		
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Telah Terima Dari</big><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<div class="input-group">
					<input type="text" name="TerimaDari" id="TerimaDari" class="form-control"
					value="<?php echo (ISSET($result->TerimaDari) ? $result->TerimaDari : "") ?>"
					<?php echo (ISSET($result->Jenis) && $result->Jenis=='REKENING VA' ? "readonly" : "") ?> required>
					<span class="input-group-btn">
						<button id="btnBrowseDealer" class="btn btn-default" type="button" onclick="javascript:ShowModalDealer()" <?php echo (ISSET($result->Jenis) && $result->Jenis=='REKENING BANK' ? '' : 'disabled') ?>>Pilih Dealer</button>
						<button id="btnBrowseSupplier" class="btn btn-default" type="button" onclick="javascript:ShowModalSupplier()" <?php echo (ISSET($result->Jenis) && $result->Jenis=='REKENING BANK' ? '' : 'disabled') ?>>Pilih Supplier</button>
					</span>
				</div><!-- /input-group -->
			</div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"><big>NPWP</big><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="NPWP" id="NPWP" class="form-control" pattern=".{15,30}" value="<?php echo (ISSET($result->NPWP) ? $result->NPWP : "") ?>"
				placeholder="Minimal 15 Karakter" <?php echo (ISSET($result->NPWP) && $result->Jenis=="REKENING VA" ? "readonly" : "") ?> required>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Ditransfer Ke</big><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="text" name="DitransferKe" id="DitransferKe" class="form-control" value="<?php echo (ISSET($result->DitransferKe) ? $result->DitransferKe : "") ?>" <?php echo (ISSET($result->Jenis) && $result->Jenis=="REKENING VA" ? "readonly" : "") ?> required>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Email</big><br><small><em>Required</em></small></div>
			<div class="col-9 col-m-8">
				<input type="email" name="Email" id="Email" class="form-control" value="<?php echo (ISSET($result->Email) ? $result->Email : "") ?>" <?php echo (ISSET($result->Jenis) && $result->Jenis=="REKENING VA" ? "readonly" : "") ?> required>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><big>Untuk Pembayaran</big><br><small><em>Optional</em></small></div>
			<div class="col-9 col-m-8">
				<input type="hidden" name="KdRef" id="KdRef" value="<?php echo (ISSET($result->KdRef) ? $result->KdRef : "") ?>">
				<textarea name="UntukPembayaran" class="form-control"><?php echo (ISSET($result->UntukPembayaran) ? str_replace("<BR>","\n",$result->UntukPembayaran) : "") ?></textarea>
			</div>
		</div>
		<div class="row">
			<div class="col-3 col-m-4"><big>Keterangan Internal</big><br><small><em>Optional</em></small></div>
			<div class="col-9 col-m-8">
				<textarea name="KeteranganInternal" class="form-control"><?php echo (ISSET($result->KeteranganInternal) ? str_replace("<BR>","\n",$result->KeteranganInternal) : "") ?></textarea>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"><big>Total</big><br><small><em>Required</em></small></div>
			<div class="col-3 col-m-2">
				<input type="text" name="Total" id="Total" class="form-control numeric" placeholder="0" value="<?php echo (ISSET($result->Total) ? number_format($result->Total) : "") ?>" required>
			</div>
		</div>
		
		<div class="row">
			<div class="col-3 col-m-4"></div>
			<div class="col-9 col-m-8">
				<?php if(ISSET($result->NoKwitansi)){ ?>
					<input type="submit" name="save" id="btn_save" class="btn btn-dark" value="UPDATE">
					<?php }else{ ?>
					<input type="submit" name="save" id="btn_save" class="btn btn-dark" value="SAVE" onclick="javascript:addnew(0)">
					<input type="submit" name="save_new" id="btn_save_new" class="btn btn-dark" value="SAVE & CREATE NEW" onclick="javascript:addnew(1)">
				<?php } ?>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>



<!-- Modal -->
<div class="modal fade" id="modal_dealer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" style="text-align: center;"><strong>PILIH DEALER</strong></h4>
			</div>
			<div class="modal-body">
				<table id="table_dealer" class="table table-bordered">
					<thead>
						<tr>
							<th scope="col" width="20px" class="no-sort">No</th>
							<th scope="col" width="100px">Kode Pelanggan</th>
							<th scope="col" width="*">Nama Pelanggan</th>
							<th scope="col" width="10px" class="no-sort"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td align="center" colspan="4">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal -->
<div class="modal fade" id="modal_va" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" style="text-align: center;"><strong>PILIH DEALER</strong></h4>
			</div>
			<div class="modal-body">
				<table id="table_va" class="table table-bordered">
					<thead>
						<tr>
							<th width="20px" class="no-sort">No</th>
							<th width="200px">NO VA</th>
							<th width="100px">Kode Pelanggan</th>
							<th width="*">Nama Pelanggan</th>
							<th width="10px" class="no-sort"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td align="center" colspan="5">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_supplier" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><center><b>PILIH SUPPLIER</b></center></h4>
			</div>
			<div class="modal-body">
				<table id="table_supplier" class="table table-bordered">
					<thead>
						<tr>
							<th width="20px" class="no-sort">No</th>
							<th width="100px">Kode Supplier</th>
							<th width="*">Nama Supplier</th>
							<th width="10px" class="no-sort"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td align="center" colspan="4">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_bank" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" style="text-align: center;"><strong>PILIH BANK</strong></h4>
			</div>
			<div class="modal-body">
				<table id="table_bank" class="table table-bordered">
					<thead>
						<tr>
							<th scope="col" width="20px" class="no-sort">No</th>
							<th scope="col" width="*">Pemilik</th>
							<th scope="col" width="100px">Cabang</th>
							<th scope="col" width="100px">No Rekening</th>
							<th scope="col" width="10px" class="no-sort"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td align="center" colspan="5">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_bukti" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><center><b>PILIH BUKTI BANK</b></center></h4>
			</div>
			<div class="modal-body">
				<table id="table_bukti" class="table table-bordered">
					<thead>
						<tr>
							<th width="20px" class="no-sort">No</th>
							<th width="100px">Tgl Trans</th>
							<th width="*">No Bukti</th>
							<th width="80px">Kode Pelanggan</th>
							<th width="*">Nama Pelanggan</th>
							<th width="120px">Total</th>
							<th width="10px" class="no-sort"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td align="center" colspan="5">Loading...</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	
	let add_new = 0;
	
	let table_dealer;
	let table_va;
	let table_bank;
	let table_bukti;
	let table_supplier;
	
	let setting;
	
	let branch_code = '<?php echo $_SESSION['logged_in']['branch_id'] ?>';
	
	function Browse(div){
		$('#KdRef').val('');
		$('#TerimaDari').val('');
		$('#DitransferKe').val('');
		$('#NPWP').val('');
		$('#Email').val('');
		$('#DitransferKe').prop('readonly',true);
		$('#TerimaDari').prop('readonly',true);
		$('#NPWP').prop('readonly',true);
		$('#Email').prop('readonly',true);
		$('#btnBrowseDealer').prop('disabled',true);
		$('#btnBrowseSupplier').prop('disabled',true);
		if(div==1){
			ShowModalBank();
		}
		else if(div==2){
			ShowModalVA();
		}
		else{
			$('#KdRef').val('');
			$('#TerimaDari').val('');
			$('#DitransferKe').val('');
			$('#NPWP').val('');
			$('#Email').val('');
			$('#TerimaDari').prop('readonly',false);
			$('#NPWP').prop('readonly',false);
			$('#DitransferKe').prop('readonly',false);
			$('#Email').prop('readonly',false);
			setTimeout(function() {$('#TerimaDari').focus()}, 300);
		}
	}
	
	function ShowModalDealer(){
		init_table_dealer();
		$('#modal_dealer').modal('show');
	}
	function ShowModalVA(){
		init_table_va();
		$('#modal_va').modal('show');
	}
	function ShowModalBank(){
		init_table_bank();
		$('#modal_bank').modal('show');
	}
	function ShowModalBukti(){
		init_table_bukti();
		$('#modal_bukti').modal('show');
	}
	function ShowModalSupplier(){
		init_table_supplier();
		$('#modal_supplier').modal('show');
	}
	function PilihRekening(pemilik, cabang, norekening){
		$('#KdRef').val('');
		$('#Email').val('');
		$('#Email').prop('readonly',false);
		$('#DitransferKe').val(pemilik+' CABANG '+cabang+' '+norekening);
		$('#TerimaDari').prop('readonly',false);
		$('#NPWP').prop('readonly',false);
		$('#btnBrowseDealer').prop('disabled',false);
		$('#btnBrowseSupplier').prop('disabled',false);
		$('#modal_bank').modal('hide');
	}
	function PilihVA(no_va, kd_plg, nm_plg, npwp, email, wilayah){
		$('#Wilayah').val(wilayah);
		$('#KdRef').val(kd_plg);
		$('#TerimaDari').val(nm_plg);
		$('#NPWP').val(npwp);
		$('#Email').val(email);
		$('#DitransferKe').val('BCA VA : '+setting.VA_BCA+'-'+no_va.substr(0,5)+'-'+no_va.substr(5,5)+' ATAS NAMA '+nm_plg);
		$('#NPWP').prop('readonly',true);
		$('#modal_va').modal('hide');
	}
	function PilihDealer(kd_plg, nm_plg, npwp, email, wilayah){
		$('#Wilayah').val(wilayah);
		$('#KdRef').val(kd_plg);
		$('#TerimaDari').val(nm_plg);
		$('#NPWP').val(npwp);
		$('#Email').val(email);
		$('#TerimaDari').prop('readonly',true);
		$('#NPWP').prop('readonly',true);
		$('#Email').prop('readonly',true);
		$('#modal_dealer').modal('hide');
	}
	function PilihSupplier(kd_sup, nm_sup, npwp, email, wilayah){
		$('#Wilayah').val(wilayah);
		$('#KdRef').val(kd_sup);
		$('#TerimaDari').val(nm_sup);
		$('#NPWP').val(npwp);
		$('#Email').val(email);
		$('#TerimaDari').prop('readonly',true);
		$('#NPWP').prop('readonly',true);
		$('#Email').prop('readonly',true);
		$('#modal_supplier').modal('hide');
	}
	
	function PilihBukti(no_bukti, total, kd_plg, nm_plg, no_va, npwp, email){
	
		$('#DitransferKe').prop('readonly',true);
		$('#TerimaDari').prop('readonly',true);
		$('#NPWP').prop('readonly',true);
		$('#Email').prop('readonly',true);
		
		$('#option2').prop('checked', true);
		
		$('#NoBukti').val(no_bukti);
		$('#Total').val(total);
		$('#KdRef').val(kd_plg);
		$('#TerimaDari').val(nm_plg);
		$('#NPWP').val(npwp);
		$('#Email').val(email);
		$('#DitransferKe').val('BCA VA : '+setting.VA_BCA+'-'+no_va.substr(0,5)+'-'+no_va.substr(5,5)+' ATAS NAMA '+nm_plg);
		$('#modal_bukti').modal('hide');
	}
	function GenerateAutoNoKwitansi(){
		var TglKwitansi = $('#TglKwitansi').val();
		if(TglKwitansi!=''){
			var tgl = new Date(TglKwitansi);
			var th = tgl.getFullYear();
			var bl = tgl.getMonth()+1;
			bl = (bl<10) ? '0'+bl.toString() : bl.toString();
			// alert(th);
			// alert(bl);
			$.post("<?php echo site_url('KwitansiLainLainV2/GenerateAutoNoKwitansi'); ?>", {
				th : th,
				bl : bl,
				BranchCode : branch_code
				}, function(data){
				// console.log(data);
				if (data=="FAILED") {
					alert('No Kwitansi tidak bisa auto generate!');
				}
				else{
					$('#NoKwitansi').val(data+'/'+setting.INVOICECODE+'-'+branch_code+'/'+bl+'/'+th);
					$('#btn_save').prop('disabled',false);
					$('#btn_save_new').prop('disabled',false);
				}
			});
		}
	}
	
	function ganti_cabang(){
		branch_code = $('#branch_code').val();
		//LoadData();
		GenerateAutoNoKwitansi();
	}
	
	function addnew(n){
		add_new = n;
	}
	
	function format_currency(x){
		var str = x.toString().replace(/[^0-9.]/g,'');
		x = str.split('.');
		x1 = x[0].length > 0 ? x[0] : '0';
		x2 = x.length > 1 ? '.' + x[1] : ''; 
		var rgx = /(\d+)(\d{3})/; 
		while (rgx.test(x1)) { 
			x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
		} 
		return x1 + x2;
	}
	
	function LoadData(){
		$('.loading').show();
		$.ajax({
			data      	: { 'BranchCode' : branch_code},
			url			: '<?php echo site_url('KwitansiLainLainV2/LoadData') ?>',
			type		: 'POST',
			dataType  	: 'json',
			success   	: function(result) {
				// console.log(result);
				$('.loading').hide();
				if(result.result=='SUCCESS'){
					setting = result.setting;
					
					<?php if(!ISSET($result->NoKwitansi)){ ?>
						GenerateAutoNoKwitansi(); 
					<?php } ?>
					
				}
				else{
					alert(result.msg+'\n'+result.error.replace(/\\n/g,"\n"));
				}
			}
		});
	}
	
	function init_table_dealer(){
	table_dealer = $('#table_dealer').DataTable({
		"retrieve": true,
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"order": [1, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": '<?php echo base_url('KwitansiLainLainV2/datatable_dealer') ?>',
			"type": "GET",
			"datatype": "json",
			"data": function (data) {
				data.branch_code = branch_code;
			}
		},
		"initComplete": function() {
			$('#table_dealer_filter input').unbind();
			$('#table_dealer_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					table_dealer.search(this.value).draw();   
				}
			}); 
		},
	});
	}
	
	function init_table_va(){
	table_va = $('#table_va').DataTable({
		"retrieve": true,
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"order": [3, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": '<?php echo base_url('KwitansiLainLainV2/datatable_va') ?>',
			"type": "GET",
			"datatype": "json",
			"data": function (data) {
				data.branch_code = branch_code;
			}
		},
		"initComplete": function() {
			$('#table_va_filter input').unbind();
			$('#table_va_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					table_va.search(this.value).draw();   
				}
			}); 
		},
	});
	}
	
	function init_table_bank(){
	table_bank = $('#table_bank').DataTable({
		"retrieve": true,
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false }
		],
		"order": [1, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": '<?php echo base_url('KwitansiLainLainV2/datatable_bank') ?>',
			"type": "GET",
			"datatype": "json",
			"data": function (data) {
				data.branch_code = branch_code;
			}
		},
		"initComplete": function() {
			$('#table_bank_filter input').unbind();
			$('#table_bank_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					table_bank.search(this.value).draw();   
				}
			}); 
		},
	});
	}
	
	function init_table_bukti(){
	table_bukti = $('#table_bukti').DataTable({
		"retrieve": true,
		"pageLength"    : 10,
		"searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		{ targets: 'col-hide', visible: false },
		{ className: "text-right", "targets": [ 5 ] }
		],
		"order": [1, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": '<?php echo base_url('KwitansiLainLainV2/datatable_bukti') ?>',
			"type": "GET",
			"datatype": "json",
			"data": function (data) {
				data.branch_code = branch_code;
			}
		},
		"initComplete": function() {
			$('#table_bukti_filter input').unbind();
			$('#table_bukti_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					table_bukti.search(this.value).draw();
				}
			}); 
		},
	});
	}
	
	function init_table_supplier(){
	table_supplier = $('#table_supplier').DataTable({
		"retrieve": true,
		"pageLength"    : 10,
		// "searching"     : true,
		"lengthChange"  : false,
		"columnDefs": [
		{ targets: 'no-sort', orderable: false },
		// { targets: 'col-hide', visible: false },
		{ className: "text-right", "targets": [ 3 ] }
		],
		"order": [1, 'asc'],
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": '<?php echo base_url('KwitansiLainLainV2/datatable_supplier') ?>',
			"type": "GET",
			"datatype": "json",
			"data": function (data) {
				data.branch_code = branch_code;
			}
		},
		"initComplete": function() {
			$('#table_supplier_filter input').unbind();
			$('#table_supplier_filter input').bind('keyup', function(e) {
				if(e.keyCode == 13) {
					table_supplier.search(this.value).draw();
				}
			}); 
		},
	});
	}
	
	
	$(document).on("input", ".numeric", function() {
		this.value = format_currency(this.value);
	});
	
	$(document).ready(function() {
		$('.dtpicker').datepicker({
			autoclose: true,
			format: "dd-M-yyyy"
		});
		
		$('#dtbtn').click(function() {
			$('.dtpicker').datepicker('show');
		});
		
		/*
		table_dealer.on('order.dt search.dt', function () {
			let i = 1;
			table_dealer.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		table_va.on('order.dt search.dt', function () {
			let i = 1;
			table_va.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		table_bank.on('order.dt search.dt', function () {
			let i = 1;
			table_bank.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		table_bukti.on('order.dt search.dt', function () {
			let i = 1;
			table_bukti.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		
		table_supplier.on('order.dt search.dt', function () {
			let i = 1;
			table_supplier.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
				this.data(i++);
			});
		}).draw();
		*/
		// $('#modal_va').on('show.bs.modal', function () {
		// // $('#modal_view').modal('show');
		// })
		
		
		<?php
			if(!ISSET($result->NoKwitansi)){
			?>
			$( "#NoKwitansi").focus(function() {
				$('#btn_save').prop('disabled',true);
				$('#btn_save_new').prop('disabled',true);
			});
			
			$("#NoKwitansi").blur(function(){
				var NoKwitansi = $('#NoKwitansi').val();
				$.post("<?php echo site_url('KwitansiLainLainV2/Check'); ?>", {
					NoKwitansi : NoKwitansi,
					BranchCode : branch_code,
					}, function(data){
					console.log(data);
					if (data=="FAILED") {
						alert('No Kwitansi sudah ada atau No Kwitansi tidak sesuai format!');
						$('#btn_save').prop('disabled',true);
						$('#btn_save_new').prop('disabled',true);
						GenerateAutoNoKwitansi();
					}
					else{
						$('#btn_save').prop('disabled',false);
						$('#btn_save_new').prop('disabled',false);
					}
				});
			});
			
			$("#TglKwitansi").change(function(){
				GenerateAutoNoKwitansi();
			});
			<?php
			}
		?>
		$("#form_kwitansi_add").submit(function() {		
			if($('#DitransferKe').val()=='' || $('#TerimaDari').val()==''){
				alert('Masih ada data kosong!');
				return false;
			}
			
			if($('#Total').val()==0){
				alert('Total kwitansi wajib diisi!');
				return false;
			}
			$("#branch_code").prop("disabled", false);
			var act = $(this).attr('action');
			var form_data = new FormData(this);
			var c = confirm("Apakah data sudah benar?");
			if(c== true){
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
							alert('Kwitansi berhasil disimpan');
							if(add_new==1){
								location.reload();
							}
							else{
								window.location.href='<?php echo site_url('KwitansiLainLainV2') ?>';
							}
						}
						else{
							alert(res.msg+'\n'+res.error.replace(/\\n/g,"\n"));
						}
					}
				});
			}
			event.preventDefault();
		});
		LoadData();
	});
</script>

