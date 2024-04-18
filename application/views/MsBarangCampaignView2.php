<style>
	#table-primary>tbody {
	font: normal medium/1.4 sans-serif;
	}
	
	#table-primary {
	border-collapse: collapse;
	width: 100%;
	}
	
	#table-primary th,
	td {
	padding: 0.25rem;
	text-align: left;
	border: 1px solid #ccc;
	}
	
	#table-primary th {
	background: #bfbfbf;
	}
	
	#table-primary>tbody>tr.zebra-even {
	background-color: #fff;
	}
	
	#table-primary>tbody>tr.zebra-odd {
	background-color: #EEEEEE;
	}
	
	.filterDropdown {
	width: 100%;
	background-color: #ffffcc;
	}
	
	.filterText {
	width: 75%;
	background-color: #ffffcc;
	}
	
	.title {
	font-size: 15pt;
	font-weight: bold;
	text-align: center;
	}
	
	.text-right{
		text-align: right;
	}
</style>

<script>
	function hitungTotAvg($index, $kota, $bedahari) {
		$('#tot_avg_'+$index+'_'+$kota).val($('#avg_rounded_'+$index+'_'+$kota).val()*$bedahari);
		// validate();
	}
	
	function hitungAvgRounded($index, $kota, $bedahari) {
		// $('#avg_rounded_'+$index+'_'+$kota).val(Math.floor($('#tot_avg_'+$index+'_'+$kota).val()/$bedahari));
		$('#avg_rounded_'+$index+'_'+$kota).val($('#tot_avg_'+$index+'_'+$kota).val()/$bedahari);
		// validate();
	}
	
	function validate() {
		var valid = true;
		$("#tbodyBarangCampaign tr").each(function() {
			// alert('masuk');
			var flag = $(this).find('input[type="radio"]');
			if(flag.is(':checked')==true){
				kdbrgFormat = flag.val().split(",")[1].replace(/\//gi, "_");
				kdbrgFormatted = kdbrgFormat.replace(/[^A-Za-z0-9\-\_]/g, '');
				wilayah = flag.val().split(",")[7].replace(/[^A-Za-z0-9\-\_]/g, '');
				
				var totalAverage = $(this).find('.tot_avg_'+wilayah+'_'+kdbrgFormatted);
				// alert('.tot_avg_'+flag.val().split(",")[7]+'_'+kdbrgFormatted);
				// alert('.tot_avg_'+flag.val().split(",")[7]+'_'+kdbrgFormatted);
				if(totalAverage.val()!=undefined){
					valid = valid && checkEmpty(totalAverage);
					
					// var detailCampaign = $(this).find('.detail-campaign');
					// var oldDetailCampaign = $(this).find('.old-detail-campaign');
					
					// // alert(totalAverage.val());
					// $(totalAverage).change(function() {
					// 	$(detailCampaign).val(oldDetailCampaign.val() + $(this).val());
					// });
				}
			}
			
			
			
		});
		
		$("#btnSubmit").attr("disabled",true);
		$("#btnSubmit").prop("disabled",true);
		$("#btnEdit").prop("disabled", true);
		$("#btnEdit").attr("disabled", true);
		
		if(valid) {
			<?php if($status == 'baru'){ ?>
				$("#btnSubmit").attr("disabled",false);
				$("#btnSubmit").prop("disabled",false);
				<?php }else{  ?>
				$("#btnEdit").prop("disabled", false);
				$("#btnEdit").attr("disabled", false);
			<?php }  ?>
			
		}	
	}
	
	function checkEmpty(obj) {
		var name = $(obj).attr("name");
		$("."+name+"-validation").html("");	
		$(obj).css("border","");
		if($(obj).val() == "" || $(obj).val() < 0) {
			$(obj).css("border","#FF0000 1px solid");
			$("."+name+"-validation").html("Required");
			return false;
		}
		
		return true;	
	}
	
	$(document).ready(function() {
		
		$(function() {
			$('input:radio[type="radio"]').change(function() {
				var val = $(this).val();
				// alert(val);
				if ($(this).val().split(",")[2] != '') {
					$('input[type="radio"]').each(function () {
						if(this.value == val)
						{
							kdbrgFormat = $(this).val().split(",")[1].replace(/\//gi, "_");
							kdbrgFormatted = kdbrgFormat.replace(/[^A-Za-z0-9\-\_]/g, '');
							kota = $(this).val().split(",")[7].replace(/[^A-Za-z0-9\-\_]/g, '');
							
							$('.avg_rounded_'+kota+'_'+kdbrgFormatted).val(0);
							$('.tot_avg_'+kota+'_'+kdbrgFormatted).val(0);
							$('.avg_rounded_'+kota+'_'+kdbrgFormatted).prop('disabled',true);
							$('.tot_avg_'+kota+'_'+kdbrgFormatted).prop('disabled',true);
							$('.tot_avg_'+kota+'_'+kdbrgFormatted).css("border","");
							validate();
						}
					})
					
					} else {
					$('input[type="radio"]').each(function () {
						if(this.value == val)
						{	
							kdbrgFormat = $(this).val().split(",")[1].replace(/\//gi, "_");
							kdbrgFormatted = kdbrgFormat.replace(/[^A-Za-z0-9\-\_]/g, '');
							kota = $(this).val().split(",")[7].replace(/[^A-Za-z0-9\-\_]/g, '');
							
							// alert('.tot_avg_'+kota+'_'+kdbrgFormatted)
							
							$('.avg_rounded_'+kota+'_'+kdbrgFormatted).prop('disabled',false);
							$('.tot_avg_'+kota+'_'+kdbrgFormatted).prop('disabled',false);
							validate();
						}
					})
					
				}
			});
		});
		
		$("#tbodyBarangCampaign tr").each(function() {
			var flag = $(this).find('input[type="radio"]');
			
			if(flag.is(':checked')==true){
				kdbrgFormat = flag.val().split(",")[1].replace(/\//gi, "_");
				kdbrgFormatted = kdbrgFormat.replace(/[^A-Za-z0-9\-\_]/g, '');
				var totalAverage = $(this).find('.tot_avg_'+flag.val().split(",")[7]+'_'+kdbrgFormatted);
				// alert(totalAverage.val())
				if(totalAverage.val()!=undefined && totalAverage.val()==0 && totalAverage.val()==''  ){
					<?php if($status == 'baru'){ ?>
						$("#btnSubmit").prop("disabled", true);
						$("#btnSubmit").attr("disabled", true);
						$("#btnEdit").prop("disabled", true);
						$("#btnEdit").attr("disabled", true);
						<?php }else{  ?>
						$("#btnSubmit").prop("disabled", true);
						$("#btnSubmit").attr("disabled", true);
						$("#btnEdit").prop("disabled", true);
						$("#btnEdit").attr("disabled", true);
					<?php }  ?>
					
					return false;
				}
			}
			
			<?php if($status == 'baru'){ ?>
				// alert('masuk')
				$("#btnSubmit").prop("disabled", false);
				$("#btnSubmit").attr("disabled", false);
				$("#btnEdit").prop("disabled", true);
				$("#btnEdit").attr("disabled", true);
				<?php }else{  ?>
				$("#btnSubmit").prop("disabled", true);
				$("#btnSubmit").attr("disabled", true);
				$("#btnEdit").prop("disabled", false);
				$("#btnEdit").attr("disabled", false);
			<?php }  ?>
			
			
		});
		
		
		
		var c = 0;
		$("#table-primary tbody tr").each(function() {
			var $item = $("td:first", this);
			var $prev = $(this).prev().find('td:first');
			
			if ($prev.length && $prev.text().trim() != $item.text().trim()) {
				c = 1 - c;
			}
			$(this).addClass(['zebra-even', 'zebra-odd'][c]);
		});
		
		$("#btnAddDt").click(function() {
			addRow();
		});
		
		$("#btnSubmit").click(function(){
			var isDisabled = $("#btnSubmit").prop('disabled');
			if(!isDisabled){
				$("#FormBarang").submit();
			}
		});
		
		$("#btnEdit").click(function(){
			var isDisabled = $("#btnEdit").prop('disabled');
			if(!isDisabled){
				$("#FormBarang").attr("action", '<?php echo site_url(); ?>' + "/PersiapanBarangCampaign/EditPersiapanCampaignFinal");
				$("#FormBarang").submit();
			}
		});
		
		$("#btnClose").click(function(){
			window.top.close();
		});
		
		
		$("#tbodyBarangCampaign tr").each(function() {
			var flag = $(this).find('input[type="radio"]');
			// if(flag.is(':checked')==true){
			kdbrgFormat = flag.val().split(",")[1].replace(/\//gi, "_");
			kdbrgFormatted = kdbrgFormat.replace(/[^A-Za-z0-9\-\_]/g, '');
			wilayah = flag.val().split(",")[7].replace(/[^A-Za-z0-9\-\_]/g, '');
			var bedahari = flag.val().split(",")[6];
			var avgRounded =  $(this).find('.avg_rounded_'+wilayah+'_'+kdbrgFormatted);
			var totalAverage = $(this).find('.tot_avg_'+wilayah+'_'+kdbrgFormatted);
			
			// console.log('.tot_avg_'+wilayah+'_'+kdbrgFormatted);
			// console.log('flag',wilayah);
			
			// var totalAverage = $(this).find('.tot_avg');
			// }
			var detailCampaign = $(this).find('.detail-campaign');
			var oldDetailCampaign = $(this).find('.old-detail-campaign');
			
			$(totalAverage).change(function() {
				// avgRounded.val(Math.floor($(this).val()/bedahari));
				// totalAverage.val(avgRounded.val()*bedahari);
				avgRounded.val($(this).val()/bedahari);
				// totalAverage.val(avgRounded.val()*bedahari);
				
				$(detailCampaign).val(oldDetailCampaign.val() + avgRounded.val() + ',' + totalAverage.val());
				
				
				console.log('.tot_avg_'+flag.val().split(",")[7]+'_'+kdbrgFormatted);
			});
			
			$(avgRounded).change(function() {
				totalAverage.val($(this).val()*bedahari);
				// alert(totalAverage.val());
				$(detailCampaign).val(oldDetailCampaign.val() + avgRounded.val() + ',' + totalAverage.val());
			});
			
			
		});
		
		<?php
			if($status=='view'){
			?>
			$("#btnSubmit").hide();
			$("#btnEdit").hide();
			$("#btnClose").show();
			<?php
				}else{
			?>
			$("#btnSubmit").show();
			$("#btnEdit").show();
			$("#btnClose").hide();
			<?php
			}
		?>
	});
</script>

<?php
	$view_only = ($status=='view') ? "disabled" : "";
?>

<div class="container">
	
	<div class="title">PERSIAPAN BARANG CAMPAIGN</div>
	<h1 style="text-align:center;"><?php echo ($headers[0]->CampaignName); ?></h1>
	<div class="form">
		<?php echo form_open('PersiapanBarangCampaign/SimpanPersiapanCampaignFinal', array("id" => "FormBarang")); ?>
		<?php foreach ($data  as $dta) {
			foreach ($dta as $dt) { ?>
			<input type="hidden" name="KdBrgDetailLengkap[]" value="<?php echo trim($dt->Kd_Brg); ?>">
			<input type="hidden" name="KotaDetailLengkap[]" value="<?php echo str_replace(' ', '', $dt->Kota); ?>">
			<input type="hidden" name="JnsTrxDetailLengkap[]" value="<?php echo trim($dt->Jns_Trx); ?>">
			<input type="hidden" name="NmTrxDetailLengkap[]" value="<?php echo trim($dt->Nm_Trx); ?>">
			<input type="hidden" name="TotalJualDetailLengkap[]" value="<?php echo trim($dt->Total_Jual); ?>">
			<input type="hidden" name="TotalHariDetailLengkap[]" value="<?php echo trim($dt->TotalHari); ?>">
			<input type="hidden" name="AvgRoundedDetailLengkap[]" value="<?php echo trim($dt->avg_rounded); ?>">
			<input type="hidden" name="AvgHarianPerThnDetailLengkap[]" value="<?php echo trim($dt->avg_harian_perthn); ?>">
			<input type="hidden" name="TotAvgDetailLengkap[]" value="<?php echo trim($dt->tot_avg); ?>">
			<input type="hidden" name="Kd_LokasiDetailLengkap[]" value="<?php echo trim($dt->Kd_Lokasi); ?>">
			
			<?php }
		} ?>
		
		<?php foreach ($headers as $header) { ?>
			<!--input type="hidden" name="CampaignID[]" value="<?php //echo ($status == 'edit' ? $header->CampaignID : ''); ?>"-->
			<input type="hidden" name="CampaignID[]" value="<?php echo $CampaignID ?>">
			<input type="hidden" name="CampaignName[]" value="<?php echo ($header->CampaignName); ?>">
			<input type="hidden" name="ProductID[]" value="<?php echo ($header->ProductID); ?>">
			<input type="hidden" name="Division[]" value="<?php echo ($header->Division); ?>">
			<input type="hidden" name="CampaignStart[]" value="<?php echo ($header->CampaignStart); ?>">
			<input type="hidden" name="CampaignEnd[]" value="<?php echo ($header->CampaignEnd); ?>">
			<input type="hidden" name="JumlahHari[]" value="<?php echo ($header->JumlahHari); ?>">
			<!-- <input type="hidden" name="header[]" value="<?php // echo trim($header->CampaignName).','.trim($header->ProductID).','.trim($header->Division);
			?>"> -->
		<?php } ?>
		
		<?php if (isset($wilayahInclude)) {
			foreach ($wilayahInclude as $include) { ?>
			<input type="hidden" name="wilayahInclude[]" value="<?php echo ($include); ?>">
			<?php }
		} ?>
		
		<?php if (isset($wilayahIncludeKdLokasi)) {
			foreach ($wilayahIncludeKdLokasi as $kdlokasi) { ?>
			<input type="hidden" name="wilayahIncludeKdLokasi[]" value="<?php echo ($kdlokasi); ?>">
			<?php }
		} ?>
		
		<ul class="nav nav-tabs">
			<?php foreach ($barang as $index => $tab) :
				if ($index == 0) { ?>
				<li class="active"><a data-toggle="tab" href="#menu<?php echo $index; ?>"><?php echo $tab; ?></a></li>
				<?php } else { ?>
				<li><a data-toggle="tab" href="#menu<?php echo $index; ?>"><?php echo $tab; ?></a></li>
				<?php }
			endforeach; ?>
		</ul>
		
		<div class="tab-content">
			
			<?php foreach ($barang as $index => $tab) :
				?>
				
				
				<div id="menu<?php echo $index; ?>" class="tab-pane fade <?php if ($index == 0) echo "in active" ?>">
					<h3>
						Mulai : <?php echo ($headers[$index]->CampaignStart); ?> - <?php echo ($headers[$index]->CampaignEnd); ?>
						&nbsp;&nbsp;&nbsp;
						Jumlah Hari : <?php echo ($headers[$index]->JumlahHari); ?>
					</h3>
					<table id="table-primary" class="table table-striped table-bordered" cellspacing="0">
						<thead id="theadBarangCampaign">
							<tr>
								<th scope="col" style="border:1px solid #ccc;">Wilayah</th>
								<th scope="col" style="border:1px solid #ccc;">Kode Barang</th>
								<th scope="col" style="border:1px solid #ccc;">Jenis Transaksi</th>
								<th scope="col" style="border:1px solid #ccc;">Nama Transaksi</th>
								<th scope="col" style="border:1px solid #ccc;">Qty</th>
								<th scope="col" style="border:1px solid #ccc;">Jumlah Hari</th>
								<th scope="col" style="border:1px solid #ccc;">Avg Rounded</th>
								<th scope="col" style="border:1px solid #ccc;">Avg Harian Per Thn</th>
								<th scope="col" style="border:1px solid #ccc;">
								<?php echo $bedahari[$index]; ?> hari*Avg</th>
								<th scope="col" style="border:1px solid #ccc;">Pilih</th>
							</tr>
						</thead>
						<tbody id="tbodyBarangCampaign">
							<?php
							$i=0; 
							foreach ($data  as $dta) {
								
								
								foreach ($dta as $dt) {
									$isChecked = '';
									if (strcmp(str_replace(' ', '', $tab), str_replace(' ', '', $dt->Kd_Brg)) == 0) {
										if ($dt->flag == 1) {
											$isChecked = 'checked';
										}
										
										$kota = str_replace(' ', '', $dt->Kota);
										$kode = trim(preg_replace('/[^A-Za-z0-9\-\_]/','', str_replace('/', '_', $dt->Kd_Brg)));
										$value =
											trim($dt->Kd_Lokasi) . ',' .
											trim($dt->Kd_Brg) . ',' .
											trim($dt->Jns_Trx) . ','.
											trim($dt->Nm_Trx) . ',' .
											trim($dt->Total_Jual) . ',' .
											trim($dt->TotalHari). ',' .
											$bedahari[$index] . ',' .
											trim($dt->Kota) . ',' .
											trim($dt->avg_harian_perthn) . ',';
											
											
										$new_tot_avg = ($headers[$index]->JumlahHari) *  ($dt->avg_rounded);
										$new_tot_avg = ($new_tot_avg == 0) ? "" : $new_tot_avg;
										
									?>
									<tr>
										<td><?php echo $dt->Kota; ?></td>
										<td><?php echo $dt->Kd_Brg; ?></td>
										<td><?php echo $dt->Jns_Trx; ?></td>
										<td><?php echo $dt->Nm_Trx; ?></td>
										<td class="text-right"><?php echo number_format($dt->Total_Jual,0); ?></td>
										<td><?php echo $dt->TotalHari; ?></td>
										<td class="text-right"><?php if (trim($dt->Jns_Trx) != NULL) {
												echo number_format(trim($dt->avg_rounded),0);
										} else { ?>
										<input type="text"
											style="width:100px"
											name="avg_rounded"
											id="avg_rounded_<?php echo $i.'_'.$kota ?>"
											class="avg_rounded_<?php echo $kota.'_'.$kode ?>"
											value="<?php echo trim($dt->avg_rounded); ?>"
											onblur="hitungTotAvg(<?php echo $i; ?>,'<?php echo $kota ?>',<?php echo $bedahari[$index]; ?>);validate();"
											<?php if($dt->flag==0){echo "disabled";} ?>
											<?php echo $view_only ?>
											/>
										<?php } ?>
										</td>
										<td class="text-right"><?php echo $dt->avg_harian_perthn; ?></td>
										
										<td class="text-right">
											<?php if (trim($dt->Jns_Trx) != NULL) {
												echo number_format($dt->tot_avg,0);
											}
											else { ?>
											
											<input type="text"
												style="width:100px"
												onblur="hitungAvgRounded(<?php echo $i; ?>,'<?php echo $kota ?>',<?php echo $bedahari[$index]; ?>);validate();"
												name="tot_avg"
												id="tot_avg_<?php echo $i.'_'.$kota ?>"
												class="tot_avg_<?php echo $kota.'_'.$kode ?>"
												value="<?php echo $new_tot_avg; ?>"
												<?php if($dt->flag==0){echo "disabled";} ?>
												<?php echo $view_only ?>
												/>
											<?php } ?>
										</td>
										<td>
											<input type='hidden'
												class="old-detail-campaign"
												name="OldDetailCampaign[<?php echo $kota . '-' . $kode ?>]"
												value="<?php echo $value ?>" <?php echo $isChecked ?> />
											
											<input type='radio'
												class="detail-campaign"
												name="DetailCampaign[<?php echo $kota . '-' . $kode ?>]"
												value="<?php echo $value . trim($dt->avg_rounded) . ',' . trim($new_tot_avg); ?>"
												<?php echo $isChecked ?>
												<?php echo $view_only ?>
												/>
											</td>
										</tr>
										<?php
										}
										$i++;
									}
								}
							?>
							</tbody>
							
						</table>
					</div>
					<?php 
				endforeach; ?>
			</div>
			
		</div>
	</div>
	<div class="clearfix" style="height:60px"></div>
	
	<div style="position:fixed;bottom:0px;left:0px;width:100%;height:50px;background-color:navy;">
	<div style="clear:both;">
		<div style='margin:10px;float:left;'>
			<div class="btn" id="btnSubmit" name="btnSubmit">Simpan</div>
		</div>
		<div style='margin:10px;float:left;'>
			<div class="btn" id="btnEdit" name="btnEdit">Edit</div>
		</div>
		<div style='margin:10px;float:left;'>
			<div class="btn" id="btnClose" name="btnClose">Close</div>
		</div>
		<!-- <div style='margin:10px;float:left;'>
			<a href="<?php //echo (base_url('ExportData/KategoriInsentif')); ?>">
			<div class="btn" id="btnExport" name="btnExport">Export</div>
			</a>
		</div> -->
	</div>
	<?php echo form_close(); ?>
</div>
</div>