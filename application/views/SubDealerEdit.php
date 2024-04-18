
<style>
	table tr{
	font-family: Verdana;
	color:black;
	font-size: 12px;
	font-style: normal;
	text-align:left;
	}
	
	table tr td{
	padding:3px;
	}
</style>
<div class="container">
	<big><center>Edit Data Subdealer</center></big>
	<hr>
	<?php echo form_open_multipart('SubDealer/update'); ?>
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
		'GeoAddress',
		'FotoTampakDepan'
		); 
		
		// kolom ini tidak bisa diedit
		$kolom_readonly = array(
		'NamaMD',
		'CabangMD',
		'Provinsi',
		'KotamadyaKabupaten'
		); 
		
		echo "<input type='hidden' name='SubDealerId' value='".$sub_dealer->SubDealerId."'>"; // 
		echo "<input type='hidden' name='TimeStamp' value='".$sub_dealer->GFormTimeStamp."'>"; // update berdasarkan timestamp
		
		foreach($sub_dealer as $key => $val) {
			if(ISSET($val)){ // data dengan value NULL tidak ditampilkan
				$nama_kolom = $key;
				
				if(isset($kolom_label[$key])) {
					$nama_kolom = $kolom_label[$key];
				}
				
				$nama_kolom = str_replace('_',' ',$nama_kolom);
				
				$read_only= "";
				if(in_array($key,$kolom_readonly)){
					$read_only= "readonly";
				}
				
				
				if(!in_array($key,$kolom_hidden)){
					switch($key){
						
						case "TerdaftarDiMishirin": //radio button
					?>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label"><?php echo $nama_kolom ?></label>
						<div class="col-sm-10">
							<input type="radio" name="<?php echo $key ?>" value="SUDAH" <?php echo (($val=="SUDAH") ? "checked" : "") ?>> Sudah
							<input type="radio" name="<?php echo $key ?>" value="BELUM" <?php echo (($val=="BELUM") ? "checked" : "") ?>> Belum
						</div>
					</div>
					<?php
						break;
						
						default:
					?>
					<div class="form-group row">
						<label class="col-sm-2 col-form-label"><?php echo $nama_kolom ?></label>
						<div class="col-sm-10">
							<input type="text" name="<?php echo $key ?>" class="form-control" value="<?php echo $val ?>" <?php echo $read_only ?>>
						</div>
					</div>
					<?php
						break;
					}
				}
			}
		}
	?>
	
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
		<div style="clear:both;">
			<div style='margin:10px;text-align:center;'>
				<input type="submit" value="UPDATE" class="btn" style="background:green">
				<input type="button" value="CANCEL" class="btn" onclick="javascript:window.history.back()">
			</div>
		</div>
	</div>
	
	
	<?php echo form_close();?>
</div>