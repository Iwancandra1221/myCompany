<script>
	var CheckData = function() {
		var th = $("#Tahun").val();
		var bl = $("#Bulan").val();
		$("#LogOmzetNasional").hide();     
		$('tbody').html("");

		$(".loading").show();
		var csrf_bit = $("input[name=csrf_bit]").val();
		$.post("<?php echo site_url('ReportOmzet/SummaryOmzetNasional'); ?>", {
			th   : th,
			bl   : bl,
			csrf_bit:csrf_bit
		}, function(data){
			if (data.result=="sukses") {
				if (data.error=="") {

					var summary = data.data;
					var x = "";
					for(var i=0; i<summary.length; i++)
					{
						$('<tr>'
							+'<td>'+summary[i]["Wilayah"]+'</td>'
							+'<td>'+summary[i]["CreatedBy"]+'</td>'
							+'<td>'+summary[i]["CreatedDate"]+'</td>'
							+'<td style="display:none;">'+((summary[i]["LockedBy"]==1)?"LOCKED"+"<br>"+summary[i]["LockedBy"]+'<br>'+summary[i]["LockedDate"]:"NOT LOCKED")+'</td>'
							+'<td style="display:none;"><input type="button" name="locked'+i+'" kat="'+summary[i]["KategoriBrg"]+'" wil="'+summary[i]["Wilayah"]+'" value="'+((summary[i]["LockedBy"]==1)?"UNLOCK":"LOCK")+'"/></td></tr>').appendTo($('tbody'));              
					}   
					$("#LogOmzetNasional").show();         
				}
			}
			$(".loading").hide();
		}
		,'json',errorAjax);
	}

	$(document).ready(function() {
		$("#LogOmzetNasional").hide();

		var err = "<?php echo($err); ?>";
		if (err!="") {
			alert(err);
		}
		CheckData();

		$("#Tahun").change(function() {
			CheckData();
		});

		$('#Bulan').on('change', function() {
			CheckData();
		});
	} );
</script>

<style type="text/css">
	th, td { border:1px solid #000; padding: 2px 10px 2px 10px; }
</style>

<div class="container">
	<div class="page-title"><?php echo($opt);?></div>

	<?php 
	echo form_open($formURL, array("target"=>"_blank")) 
	?>
	<div class="form-container">
		<div class="row PERIODE" style="">
			<div class="col-3 col-m-4" align="right">
				Laporan
			</div>
			<div class="col-9 col-m-8">
				<select name="laporan" id="laporan" style="width:100%;" onchange="actionlaporan()">
					<option value="1">PERBANDINGAN OMZET MINGGUAN NASIONAL</option>
					<option value="2">PERBANDINGAN OMZET BULANAN PER CABANG</option>
					<option value="3">PERBANDINGAN OMZET BULANAN NASIONAL</option>
					<option value="4">PERBANDINGAN OMZET QUARTAL NASIONAL</option>
				</select>
			</div>
		</div>
		<div class="row PERIODE" style="">
			<div class="col-3 col-m-4" align="right">
				Jenis Data
			</div>
			<div class="col-9 col-m-8">
				<label style="margin-right: 20px;"><input type="radio" name="jenis_data" value="1" checked> Omzet jual - retur </label>
				<label style="margin-right: 20px;"><input type="radio" name="jenis_data" value="2"> Omzet Netto </label>
				<label style="margin-right: 20px;"><input type="radio" name="jenis_data" value="3"> Total Jual </label>
				<label style="margin-right: 20px;"><input type="radio" name="jenis_data" value="4"> Total Retur </label>
			</div>
		</div>
		<div class="row PERIODE" style="">
			<div class="col-3 col-m-4" align="right">
				Tipe Ppn
			</div>
			<div class="col-9 col-m-8">
				<label style="margin-right: 20px;"><input type="radio" name="tipe_ppn" value="1" checked> Exclude </label>
				<label style="margin-right: 20px;"><input type="radio" name="tipe_ppn" value="2" > Include </label>
			</div>
		</div>
		<div class="row PERIODE" style="">
			<div class="col-3 col-m-4" align="right">
				Partner Type
			</div>
			<div class="col-9 col-m-8">
				<select name="partner_type" style="width:100%;">
					<?php
						if($partnerType!=''){
							echo '<option value="">ALL</option>';
							foreach($partnerType['data'] as $key => $value){
								if($value['PARTNER_TYPE']!=''){
									echo '<option value="'.$value['PARTNER_TYPE'].'">'.$value['PARTNER_TYPE'].'</option>';
								}
							}
						}
					?>
					
				</select>
			</div>
		</div>
		<div class="row PERIODE" style="">
			<div class="col-3 col-m-4" align="right">
				Tahun Awal
			</div>
			<div class="col-9 col-m-8">
				<input type="number" name="year_start" value="<?php echo( (date('Y')-1) );?>" style="width:100%;color: black;">
			</div>
		</div>
		<div class="row PERIODE" style="">
			<div class="col-3 col-m-4" align="right">
				Tahun Akhir
			</div>
			<div class="col-9 col-m-8">
				<input type="number" name="year_end"  value="<?php echo(date('Y'));?>" style="width:100%;color: black;">
			</div>
		</div>
		<div id="filterlaporan">
			<div class="row PERIODE" style="">
				<div class="col-3 col-m-4" align="right">
					Bulan
				</div>
				<div class="col-9 col-m-8">
					<select name="month" style="width:100%;">
						<!-- <option value="00">GABUNGAN</option> -->
						<option value="01" <?php echo((date("m")=="01")?"selected":"")?>>JANUARI</option>
						<option value="02" <?php echo((date("m")=="02")?"selected":"")?>>FEBRUARI</option>
						<option value="03" <?php echo((date("m")=="03")?"selected":"")?>>MARET</option>
						<option value="04" <?php echo((date("m")=="04")?"selected":"")?>>APRIL</option>
						<option value="05" <?php echo((date("m")=="05")?"selected":"")?>>MEI</option>
						<option value="06" <?php echo((date("m")=="06")?"selected":"")?>>JUNI</option>
						<option value="07" <?php echo((date("m")=="07")?"selected":"")?>>JULI</option>
						<option value="08" <?php echo((date("m")=="08")?"selected":"")?>>AGUSTUS</option>
						<option value="09" <?php echo((date("m")=="09")?"selected":"")?>>SEPTEMBER</option>
						<option value="10" <?php echo((date("m")=="10")?"selected":"")?>>OKTOBER</option>
						<option value="11" <?php echo((date("m")=="11")?"selected":"")?>>NOVEMBER</option>
						<option value="12" <?php echo((date("m")=="12")?"selected":"")?>>DESEMBER</option>
						
					</select>
				</div>
			</div>

			<div class="row PERIODE" style="">
				<div id="parent-periode">
					
				</div>
			</div>
			<div class="row" align="right">
				<div class="col-12 col-m-12">
					<input type = "button" id="btn-add-periode" value="+ tambah periode"/>  
				</div>
			</div>
		</div>
		<div class="row" align="center">
			<div class="col-12 col-m-12">
				<input type = "submit" name="submit" value="EXCEL"/>  
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>

<script>
	$(document).ready(function(){
		templateChildPeriode();

		$("#btn-add-periode").click(function(){
			templateChildPeriode();
		});
		$("select[name='month']").change(function(){
			const val = $(this).val();
			if(val == '00'){
				$("#btn-add-periode").css({"display":"none"});
				$("#parent-periode").css({"display":"none"});
				// $("#parent-periode").children().remove()
			}
			else{
				$("#btn-add-periode").css({"display":"block"});
				$("#parent-periode").css({"display":"block"});
			}
		});
	});

	function actionlaporan(){
		var laporan = document.getElementById('laporan').value;
		var filterlaporan = document.getElementById('filterlaporan');

		if (laporan == 1) {
			filterlaporan.style.display = 'block';
		} else {
			filterlaporan.style.display = 'none';
		}
	}

	function templateChildPeriode(){
		var periodeKe = $(".child-periode").length;
		var date1 = 1;
		var date2 = 7;
		if(periodeKe>0){
			date1 = parseInt( $(".child-periode").eq((periodeKe-1)).find('.date1').val() );
			date2 = parseInt( $(".child-periode").eq((periodeKe-1)).find('.date2').val() );

			date1 += 7;
			date2 += 7;
		}
		


		periodeKe+=1;
		const html = `
			<div class="child-periode">
				<div class="judul-periode col-3 col-m-4" align="right">
					Periode ke-`+ periodeKe +`
				</div>
				<div class="col-3 col-m-3">
					<input class="date1" type="number" name="date1[]" value="`+ date1 +`" style="width:100%;color: black;">
				</div>
				<div class="col-1 col-m-1" align="center">s/d</div>
				<div class="col-4 col-m-3" >
					<input class="date2" type="number" name="date2[]" value="`+ date2 +`" style="width:100%;color: black;">
				</div>
				<div class="col-1 col-m-1" align="right">
					<button type="button" style="color: red;width: 100%;" onclick="btnDeletePeriode(event)">X</button>
				</div>
			</div>`;
		$("#parent-periode").append(html);
	}
	function btnDeletePeriode(event){
		var index = $(event.target).closest('.child-periode').index();
		$(".child-periode").eq(index).remove();
		const count = $(".child-periode").length;
		for(var i=0;i<count;i++){
			$(".child-periode").eq(i).children('.judul-periode').text('Periode ke-'+(i+1));
		}
	}
</script>

