<div class="container">
	<div class="row5">
		<div class="col-12">
			<div class="page-title">REQUEST PEMBELIAN BARANG IMPORT</div>
		</div>

<?php

		$url = $this->API_URL."/CekStatusDivisi/cek/".str_replace("=", "", base64_encode(rtrim($approval[0]->ApprovedByName)))."/".str_replace("=", "", base64_encode($_SESSION["logged_in"]["useremail"]))."?api=APITES";
        $hasil = file_get_contents($url);
        $count=substr_count($hasil,'(');

        if($count>0){
	        $hasil = explode('(', $hasil);
	        $hasil = (str_replace(")", "", $hasil[1]));
	    }
?>
		<div class="col-12">
				<table id="table" class="table table-striped" style="border: none;" cellspacing="0">
					<tr>
						<td width="150px">NO REQUEST</td>
						<td width="10px;">:</td>
						<td id="no_request"></td>
					</tr>
					<tr>
						<td>JENIS</td>
						<td>:</td>
						<td id="jenis"></td>
					</tr>
					<tr>
						<td>MERK</td>
						<td>:</td>
						<td id="merk"></td>
					</tr>
					<tr>
						<td>TGL DIPERLUKAN</td>
						<td>:</td>
						<td id="tgl_diperlukan"></td>
					</tr>
					<tr>
						<td>KETERANGAN </td>
						<td>:</td>
						<td id="keterangan"></td>
					</tr>
				</table>
				<div class="col-12 mt-5">
					Detail :
				</div>
				<table id="table" class="table table-striped" cellspacing="0">
					<header>
						<tr>
							<td width="50px" align="center">NO</td>
							<td>Kode Barang</td>
							<td>Nama Barang</td>
							<td width="200px" align="right">Qty</td>
						</tr>
					</header>
					<tbody id="detail_item"></tbody>
					<tfoot>
						<tr>
							<td colspan="4" id="button_action">
							</td>
						</tr>
					</tfoot>
				</table>

				Log Request Pembelian Import<br>
				Request Oleh: <b><span id="nama_pengajuan"></span> [<span id="tgl_pengajuan"></span>]</b>

				<div class="col-12" id="status"></div>
		</div>
	</div>
</div>


<script>
	head();
    function head(){

				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo $this->API_URL.'/PembelianImportApproval/ListPembelianImportApproval?api=APITES&number='.$get; ?>', 
					data  	: '',
					success : function(data) {
						obj = JSON.parse(data);

						document.getElementById('no_request').innerHTML=obj[0].Kd_Pengajuan;
						document.getElementById('jenis').innerHTML=obj[0].Kd_JenisBI;
						document.getElementById('merk').innerHTML=obj[0].Kd_MerkBI;
						document.getElementById('tgl_diperlukan').innerHTML=obj[0].Tgl_Dibutuhkan;
						document.getElementById('keterangan').innerHTML=obj[0].Keterangan;
						document.getElementById('nama_pengajuan').innerHTML=obj[0].Nm_Pengajuan;
						document.getElementById('tgl_pengajuan').innerHTML=obj[0].Entry_Time;

						detail(obj[0].Kd_Pengajuan);
						<?php
							$status = rtrim($approval[0]->BhaktiFlag);
							if($status=='UNPROCESSED'){
						?>


								if (obj[0].Kd_JenisBI != 'PRODUK' && obj[0].Kd_JenisBI != 'SPAREPART'){

									var status = "'"+obj[0].Kd_Pengajuan+"','<?php echo rtrim($approval[0]->ApprovedByName); ?>','2'";

									document.getElementById('button_action').innerHTML='<button class="btn_custom btn-primary" onclick="approval('+status+')">APPROVE</button><button class="btn_custom btn-danger" onclick="reject('+status+')">REJECT</button>';
										
								}else if('<?php echo $hasil; ?>'=='MANAGER'){
									
									var status = "'"+obj[0].Kd_Pengajuan+"','<?php echo rtrim($approval[0]->ApprovedByName); ?>',0";

									document.getElementById('button_action').innerHTML='<button class="btn_custom btn-primary" onclick="approval('+status+')">APPROVE</button><button class="btn_custom btn-danger" onclick="reject('+status+')">REJECT</button>';
								}else if('<?php echo $hasil; ?>'=='GENERAL MANAGER'){
									
									var status = "'"+obj[0].Kd_Pengajuan+"','<?php echo rtrim($approval[0]->ApprovedByName); ?>','1'";

									document.getElementById('button_action').innerHTML='<button class="btn_custom btn-primary" onclick="approval('+status+')">APPROVE</button><button class="btn_custom btn-danger" onclick="reject('+status+')">REJECT</button>';
								}


						<?php
							}
						?>
						
					}

				});		

		}


    function detail(){

				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo $this->API_URL.'/PembelianImportApproval/ListPembelianImportApprovalDetail?api=APITES&number='.$get; ?>', 
					data  	: '',
					success : function(data) {

						var isi_table = '';

						obj = JSON.parse(data);
						var no=1;

						for (var i = 0; i < obj.length; i++) {

							isi_table +='<tr>';
							isi_table +='<td align="center">'+no+'</td>';
							isi_table +='<td>'+obj[i].Kd_Brg+'</td>';
							isi_table +='<td>'+obj[i].Nm_Brg+'</td>';
							isi_table +='<td align="right">'+obj[i].Qty+'</td>';
							isi_table +='</tr>';
							no++;
						}

						document.getElementById('detail_item').innerHTML=isi_table;
						$('#table').DataTable({
				        	"pageLength": 10
				      	});
					}

				});		

		}

		function approval(a,b,c){
				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo site_url('PembelianImportApproval/ApproveRequest?api=APITES&kdreq=');?>'+a+'&empid='+b+'&gm='+c, 
					data  	: '',
					success : function(data) {

						document.getElementById('status').innerHTML=data;

					}

				});		
		}

		function reject(a,b,c){
				$.ajax({
					type 	: 'GET',	
					url 	: '<?php echo site_url('PembelianImportApproval/RejectRequest?api=APITES&kdreq=');?>'+a+'&empid='+b+'&gm='+c, 
					data  	: '',
					success : function(data) {

						document.getElementById('status').innerHTML=data;

					}

				});		
		}


</script>