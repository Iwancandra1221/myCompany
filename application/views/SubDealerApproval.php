
<style>
	h3{
	text-align: center;
	}
	
	.table{
	font-size:12px;
	}
	
	.table-border{
	border:1px solid #ddd;
	border-collapse:collapsed;
	}
	.table-border td{
	border:1px solid #ddd;
	border-collapse:collapsed;
	}
	
	
	.table tr:nth-child(even) {background-color: #f8f8f8;}
	
	.table tr td{
	padding:2px;
	}
</style>
<div class="container">
	<big><center>Approval Sub Dealer</center></big>
	<?php echo form_open_multipart('SubDealer/Approve',array('id' => 'form')); ?>
	<?php
		$kolom_label = array(
		'NamaMD'=>'Nama MD',
		'CabangMD'=>'Cabang',
		'NamaToko'=>'Nama Toko',
		'TitleToko'=>'Title Toko',
		'FotoTampakDepan'=>'Foto Tampak DEPAN TOKO',
		'NamaPemilik'=>'Nama Pemilik Toko',
		'NamaPanggilan'=>'Nama Panggilan Pemilik Toko',
		'TerdaftarDiMishirin'=>'Sudah terdaftar di Aplikasi Mishirin?',
		'EmailLoginMishirin'=>'Email Login Mishirin',
		'EmailToko'=>'Email Toko',
		'NoHP'=>'No HP',
		'NoWhatsapp'=>'No Whatsapp',
		'NoTelpToko'=>'No Telp Toko',
		'AlamatToko'=>'Alamat Toko',
		'KodePos'=>'Kode Pos',
		'KotamadyaKabupaten'=>'KotaMadya/Kabupaten'
		);
		
		// kolom ini perlu dihidden
		$kolom_hidden = array(
		'UpdatedJson',
		'GFormTimeStamp',
		'SubDealerId',
		'DataSurveyId',
		'TglMarketSurvey',
		'TimeStamp',
		'CreatedBy',
		'CreatedDate',
		'ModifiedBy',
		'ModifiedDate',
		'IsInvalid',
		'SetInvalidBy',
		'SetInvalidDate',
		'SetInvalidNote',
		'Tujuan_Form',
		'GeoStamp',
		'GeoCode',
		'GeoAddress'
		); 
		
		// kolom ini tidak bisa diedit
		$kolom_readonly = array(
		'NamaMD',
		'CabangMD',
		'Provinsi'
		); 
		
		echo "<input type='hidden' name='SubDealerId' value='".$sub_dealer->SubDealerId."'>"; // 
		echo "<input type='hidden' name='TimeStamp' value='".$sub_dealer->GFormTimeStamp."'>"; // update berdasarkan timestamp
		
		
		$new = json_decode($sub_dealer->UpdatedJson, true);
	?>
	
	<table class="table table-border">
		<tr>
			<th width="20%">KOLOM</th>
			<th width="40%">SEBELUM EDIT</th>
			<th width="40%">SESUDAH EDIT</th>
		</tr>
		<?php
			foreach($sub_dealer as $key => $val) {
				if(ISSET($val)){ // data dengan value NULL tidak ditampilkan
					$nama_kolom = $key;
					
					if(isset($kolom_label[$key])) {
						$nama_kolom = $kolom_label[$key];
					}
					$nama_kolom = str_replace('_',' ',$nama_kolom);
					
					if(!in_array($key,$kolom_hidden)){
						$edit_style = (trim($new[$key])!=trim($val)) ? "color:red" : "";
					?>
					<tr style="<?php echo $edit_style ?>">
						<td><?php echo $nama_kolom ?></td>
						<td><?php echo $val ?></td>
						<td><?php echo $new[$key] ?></td>
					</tr>
					<?php
					}
				}
			}
		?>
	</table>
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;text-align:center;'>
				<input type="submit" name="approve" value="APPROVE" class="btn" style="color:#fff;background:green">
				<input type="submit" name="reject" value="REJECT" class="btn" style="color:#fff;background:indianred">
				<input type="button" value="CANCEL" class="btn" onclick="javascript:window.history.back()">
			</div>
		</div>
	</div>
	<?php echo form_close();?>
</div>		